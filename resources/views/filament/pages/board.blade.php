<x-filament::page>

<h1>Hello from the Home Page.</h1>

  @php
    $jsonData = json_decode(file_get_contents('/Users/carlosroque/01SolarAI/04 Portfolio/01 InitialProg/project-management/resources/data.json'), true);
  @endphp

  <div id="grid" class="grid grid-cols-53 gap-0">
    @for ($i = 106; $i < 159; $i++)
      <div class="grid-cell" id="cell-{{ $i }}">
        @foreach ($jsonData as $button)
          @if ($i == (106 + $button['WeekNum']))
            <button
              class="milestone-button"
              id="button-{{ $button['ID'] }}"
              data-button-info="{{ json_encode($button) }}"
              style="background-color: {{ $button['Color'] }}; color: {{ $button['TextColor'] }};"
            >
              {{ $button['Text'] }}
            </button>
          @endif
        @endforeach
      </div>
    @endfor
    <div id="connecting-line" style="position: absolute; height: 2px; background-color: black;"></div>
  </div>

  <div id="weekday-buttons-container" class="hidden">
  </div>

  @vite(['resources/js/app.js'])

  <style>
    .grid {
      position: relative;
      display: grid;
      grid-template-columns: repeat(53, 1fr);
      gap: 0;
      border: 1px solid #ccc;
      z-index: 1;
    }

    .grid-cell {
      border-right: 1px solid #ccc;
      border-bottom: 1px solid #ccc;
      height: 50px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
      cursor: pointer;
    }

    .grid-cell:last-child {
      border-right: none;
    }

    .grid-cell:nth-last-child(-n+53) {
      border-bottom: none;
    }

    .milestone-button {
      padding: 1px;
      border: none;
      cursor: move;
      text-align: center;
      width: 40px;
      height: 30px;
      margin: 0 auto;
      position: relative;
      z-index: 3;
    }

    .bt-button {
      padding: 1px;
      color: white;
      background-color: green;
      border: none;
      cursor: pointer;
      text-align: center;
      width: 40px;
      height: 30px;
      margin: 0 auto;
      position: relative;
      z-index: 3;
    }

    .weekday-button {
      padding: 5px 10px;
      color: white;
      background-color: #6c757d;
      border: none;
      cursor: pointer;
      text-align: center;
      margin: 2px;
    }

    #weekday-buttons-container {
      position: fixed;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      z-index: 20;
      background-color: white;
      border: 1px solid #ccc;
      padding: 5px;
    }

    #weekday-buttons-container.hidden { /* More specific selector */
    display: none;
}

    .weekday-button.droppable {
      background-color: #5a6268;
    }

    .hidden {
      display: none;
    }
  </style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('#grid');
    const milestoneButtons = document.querySelectorAll('.milestone-button');
    const line = document.querySelector('#connecting-line');
    const weekdayButtonsContainer = document.getElementById('weekday-buttons-container');
    let draggedButton = null;
    let originalPosition = null;
    let potentialDropCell = null; // Variable to store the cell being hovered

    function getButtonDetails(button) {
      const buttonInfo = JSON.parse(button.dataset.buttonInfo);
      let positionText = `Week: ${buttonInfo.WeekNum}`;
      if (buttonInfo.hasOwnProperty('DayOfWeek')) {
        positionText += `, Day: ${buttonInfo.DayOfWeek}`;
      }
      return `
        Date: ${buttonInfo.Date}<br>
        Position: ${positionText}<br>
        Name: ${buttonInfo.Text}<br>
        Color: ${buttonInfo.Color}
      `;
    }

    function initializeTippy(button) {
      tippy(button, {
        content: getButtonDetails(button),
        allowHTML: true,
        theme: 'light',
        onShow(instance) {
          instance.setContent(getButtonDetails(button));
        },
      });
    }

    milestoneButtons.forEach(button => {
      initializeTippy(button);
      button.setAttribute('draggable', true);

      button.addEventListener('dragstart', handleDragStart);
      button.addEventListener('dragend', handleDragEnd);
    });

    function updateLine() {
      const buttons = Array.from(document.querySelectorAll('.milestone-button'));
      if (buttons.length < 2) return;

      const firstButton = buttons[0];
      const lastButton = buttons[buttons.length - 1];

      const firstRect = firstButton.getBoundingClientRect();
      const lastRect = lastButton.getBoundingClientRect();
      const gridRect = grid.getBoundingClientRect();

      const startX = firstRect.left + firstRect.width / 2 - gridRect.left;
      const startY = firstRect.top + firstRect.height / 2 - gridRect.top;
      const endX = lastRect.left + lastRect.width / 2 - gridRect.left;
      const endY = lastRect.top + lastRect.height / 2 - gridRect.top;

      const length = Math.sqrt(Math.pow(endX - startX, 2) + Math.pow(endY - startY, 2));
      const angle = Math.atan2(endY - startY, endX - startY) * 180 / Math.PI;

      line.style.width = `${length}px`;
      line.style.transform = `rotate(${angle}deg)`;
      line.style.transformOrigin = '0 0';
      line.style.position = 'absolute';
      line.style.top = `${startY}px`;
      line.style.left = `${startX}px`;
    }

    function handleDragStart(event) {
        draggedButton = event.target;
        const buttonInfo = JSON.parse(draggedButton.dataset.buttonInfo);
        originalPosition = {
            parent: draggedButton.parentElement,
            nextSibling: draggedButton.nextSibling,
            weekNum: buttonInfo.WeekNum
        };
        console.log("handleDragStart - originalPosition:", originalPosition);
        event.dataTransfer.setData('text/plain', event.target.id);
        event.dataTransfer.effectAllowed = 'move';

        showWeekdayButtons(event);
    }

    function handleDragEnd(event) {
        console.log("handleDragEnd - event.target:", event.target);
        const droppedOnWeekdayButton = event.target.classList.contains('weekday-button');
        console.log("handleDragEnd - droppedOnWeekdayButton:", droppedOnWeekdayButton);

        if (!droppedOnWeekdayButton) {
            // **In handleDragEnd, just check for a valid cell and store it as potentialDropCell, but don't append yet**
            const targetCell = document.elementFromPoint(event.clientX, event.clientY)?.closest('.grid-cell');
            console.log("handleDragEnd - targetCell (grid hover):", targetCell);
            if (targetCell && isThirdRow(targetCell) && !targetCell.querySelector('button')) {
                console.log("handleDragEnd - Valid grid hover, storing potentialDropCell:", targetCell.id);
                potentialDropCell = targetCell; // Store the potential cell
            } else {
                console.log("handleDragEnd - Invalid grid hover, returning to original position");
                returnToOriginalPosition(draggedButton);
                potentialDropCell = null; // Clear potential cell if invalid
            }
        } else {
            console.log("handleDragEnd - Dropped on weekday button - should be handled by weekday button's drop event");
        }

        hideWeekdayButtons(); // Hide weekday buttons in handleDragEnd as well
        draggedButton = null;
        originalPosition = null;
        updateLine();
    }


    function handleDragOver(event) {
        event.preventDefault();
        requestAnimationFrame(updateLine);
        positionWeekdayButtons(event);

        // **Update potentialDropCell on dragover while hovering over valid cells**
        const targetCell = document.elementFromPoint(event.clientX, event.clientY)?.closest('.grid-cell');
        if (targetCell && isThirdRow(targetCell) && !targetCell.querySelector('button')) {
            potentialDropCell = targetCell; // Update potentialDropCell as mouse moves over valid cells
        }
        else {
            potentialDropCell = null; // Clear potentialDropCell if mouse moves out of valid cell
        }


        const weekdayButtons = weekdayButtonsContainer.querySelectorAll('.weekday-button');
        weekdayButtons.forEach(button => {
            const buttonRect = button.getBoundingClientRect();
            if (
                event.clientX >= buttonRect.left &&
                event.clientX <= buttonRect.right &&
                event.clientY >= buttonRect.top &&
                event.clientY <= buttonRect.bottom
            ) {
                button.classList.add('droppable');
            } else {
                button.classList.remove('droppable');
            }
        });
    }

    function handleDragLeave(event) {
        const weekdayButtons = weekdayButtonsContainer.querySelectorAll('.weekday-button');
        weekdayButtons.forEach(button => button.classList.remove('droppable'));
        potentialDropCell = null; // Clear potentialDropCell when drag leaves grid
    }

    function isThirdRow(element) {
        const cellIndex = parseInt(element.id.split('-')[1]);
        return Math.floor((cellIndex - 106) / 53) === 0;
    }

    function returnToOriginalPosition(button) {
        console.log("returnToOriginalPosition called");
        console.log("originalPosition:", originalPosition);
        if (!originalPosition || originalPosition.weekNum === undefined) {
            console.error("originalPosition is invalid or weekNum is missing!");
            return;
        }
        const cellIdToFind = 106 + originalPosition.weekNum;
        console.log("cellIdToFind:", cellIdToFind);
        const originalCell = document.querySelector(`#cell-${cellIdToFind}`);
        console.log("originalCell (re-queried):", originalCell);
        if (originalCell) {
            originalCell.appendChild(button);
        } else {
            console.error("originalCell NOT FOUND (re-queried) for ID: #cell-" + cellIdToFind);
        }
        updateLine();
    }

    function showWeekdayButtons(dragEvent) {
        weekdayButtonsContainer.innerHTML = '';
        const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        for (let i = 0; i < weekdays.length; i++) {
            const weekdayButton = document.createElement('button');
            weekdayButton.className = 'weekday-button';
            weekdayButton.textContent = weekdays[i];

            weekdayButton.addEventListener('dragover', function (event) {
                event.preventDefault();
                this.classList.add('droppable');
            });

            weekdayButton.addEventListener('dragleave', function (event) {
                this.classList.remove('droppable');
            });

            weekdayButton.addEventListener('drop', function (event) {
                event.preventDefault();
                event.stopPropagation();

                // **Now, in weekday button drop, append to potentialDropCell**
                if (potentialDropCell) { // Check if potentialDropCell is set
                    console.log("weekdayButton drop - Valid weekday drop - appending to potentialDropCell:", potentialDropCell.id, "Day:", weekdays[i]);
                    potentialDropCell.appendChild(draggedButton); // Append to potentialDropCell
                    const cellId = potentialDropCell.id.split('-')[1];
                    updateButtonPosition(draggedButton, cellId, i + 1);
                    potentialDropCell = null; // Clear potentialDropCell after successful drop
                    hideWeekdayButtons();
                    updateLine();
                } else {
                    console.log("weekdayButton drop - Invalid weekday drop - no potentialDropCell set");
                    returnToOriginalPosition(draggedButton); // Or handle invalid weekday drop as needed
                    hideWeekdayButtons(); // Hide even if invalid drop
                }
            });

            weekdayButtonsContainer.appendChild(weekdayButton);
        }
        weekdayButtonsContainer.classList.remove('hidden');
        positionWeekdayButtons(dragEvent);
    }

    function positionWeekdayButtons(event) {
        const buttonRect = draggedButton.getBoundingClientRect();
        weekdayButtonsContainer.style.top = `${buttonRect.bottom + window.scrollY + 5}px`;
        weekdayButtonsContainer.style.left = `${buttonRect.left + window.scrollX}px`;
    }

    function hideWeekdayButtons() {
        console.log("hideWeekdayButtons() is being called");
        weekdayButtonsContainer.classList.add('hidden');
        console.log("weekdayButtonsContainer classList:", weekdayButtonsContainer.classList);
    }

    function updateButtonPosition(button, newPosition, dayOfWeek = null) {
        const buttonInfo = JSON.parse(button.dataset.buttonInfo);
        buttonInfo.WeekNum = parseInt(newPosition);
        if (dayOfWeek !== null) {
            buttonInfo.DayOfWeek = dayOfWeek;
        } else {
            delete buttonInfo.DayOfWeek;
        }
        button.dataset.buttonInfo = JSON.stringify(buttonInfo);
        if (button._tippy) {
            button._tippy.destroy();
        }
        initializeTippy(button);
    }


    const cells = document.querySelectorAll('.grid-cell');
    cells.forEach(cell => {
      if (isThirdRow(cell)) {
        cell.addEventListener('dragover', handleDragOver);
        cell.addEventListener('dragleave', handleDragLeave);
      }

      cell.addEventListener('click', function() {
        handleCellClick(this);
      });
    });

    function handleCellClick(cell) {
      if (isCellOnLine(cell)) {
        if (!cell.querySelector('button')) {
          const btButton = document.createElement('button');
          btButton.className = 'bt-button';
          btButton.textContent = 'BT';
          cell.appendChild(btButton);
          initializeTippy(btButton);
        }
      }
    }

    function isCellOnLine(cell) {
      const cellRect = cell.getBoundingClientRect();
      const lineRect = line.getBoundingClientRect();

      return (
        cellRect.left < lineRect.right &&
        cellRect.right > lineRect.left &&
        cellRect.top < lineRect.bottom &&
        cellRect.bottom > lineRect.top
      );
    }

    weekdayButtonsContainer.addEventListener('dragover', handleDragOver);
    weekdayButtonsContainer.addEventListener('dragleave', handleDragLeave);

    updateLine();
  });
</script>

  <script src="https://unpkg.com/@popperjs/core@2"></script>
  <script src="https://unpkg.com/tippy.js@6"></script>
</x-filament::page>
