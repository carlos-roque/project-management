<x-layout>
    <x-slot:heading>
        Home Page
    </x-slot:heading>
    <h1>Hello from the Home Page.</h1>

    <div class="container">
        <!-- Back Layer (Grid) -->
        <div class="back-layer">
            @for ($i = 0; $i < 53; $i++)
                <div class="grid-cell"></div>
            @endfor
        </div>

        <!-- Front Layer (Draggable Button) -->
        <div class="front-layer">
            <button class="milestone-button" draggable="true" data-name="My MC Button" data-color="#007bff" data-dragged-date="" data-final-date="">
                <div class="date-display"></div>
                <div class="label">MC</div>
            </button>
        </div>

        <!-- Info Dialog -->
        <div id="info-dialog" class="hidden dialog">
            <div class="dialog-content">
                <p><strong>Dragged Date:</strong> <span id="dialog-dragged-date"></span></p>
                <div class="days-container" id="dragged-days-container">
                    <div class="day-circle" data-day="Mon">M</div>
                    <div class="day-circle" data-day="Tue">T</div>
                    <div class="day-circle" data-day="Wed">W</div>
                    <div class="day-circle" data-day="Thu">T</div>
                    <div class="day-circle" data-day="Fri">F</div>
                    <div class="day-circle" data-day="Sat">S</div>
                    <div class="day-circle" data-day="Sun">S</div>
                </div>

                <!-- Calendar View -->
                <div id="calendar-container" style="margin-top: 10px;"></div>

                <button id="close-dialog">×</button>
            </div>
        </div>
    </div>

    <style>
       html, body {
            margin: 0;
            overflow-x: hidden;
        }

        .container {
            position: relative;
            width: 200%;
            height: 50px;
            left: 0;
        }

        .back-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(53, 1fr);
            border: 1px solid gray;
            z-index: 1;
        }

        .grid-cell {
            border-right: 1px solid #ccc;
            height: 100%;
        }

        .grid-cell:last-child {
            border-right: none;
        }

        .front-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            display: flex;
            align-items: center;
            overflow: hidden;
            pointer-events: none;
        }

        .milestone-button {
            width: calc(100% / 53);
            min-height: 40px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: grab;
            position: absolute;
            left: 0;
            user-select: none;
            pointer-events: auto;
            box-sizing: border-box;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2px 0;
        }

        .milestone-button .date-display {
            font-size: 12px;
            line-height: 1;
            margin-bottom: 2px;
        }

        .milestone-button .label {
            font-size: 14px;
            line-height: 1;
        }

        .milestone-button:active {
            cursor: grabbing;
        }

         .dialog {
            position: fixed;
            background-color: white;
            border: 1px solid #ccc;
            padding: 5px; /* Reduced padding */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            pointer-events: auto;
            box-sizing: border-box;
            width: 160px;  /*Reduced dialog size*/
            font-size: 12px; /* Reduced font size */
        }

        .dialog.hidden {
            display: none;
        }

        .days-container {
            display: flex;
            justify-content: space-between;
            margin: 2px 0; /* Reduced margin */
            gap: 1px;     /* Reduced gap */
        }

        .day-circle {
            width: 18px;  /* Reduced size */
            height: 18px; /* Reduced size */
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px; /* Reduced font size */
            color: #666;
            transition: all 0.2s ease;
        }

        .day-circle.active {
            background-color: #007bff !important;
            color: white !important;
        }

        .date-display {
            font-size: 12px; /* Reduced font size */
            font-weight: bold;
            margin: 2px 0;    /* Reduced margin */
            text-align: center;
        }

        #close-dialog {
            position: absolute;
            top: 2px;      /* Reduced top */
            right: 2px;    /* Reduced right */
            background: none;
            border: none;
            font-size: 14px; /* Reduced font size */
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 16px;     /* Reduced width */
            height: 16px;    /* Reduced height */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Calendar Styles */
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar-table th, .calendar-table td {
            border: 1px solid #ddd;
            padding: 1px; /* Significantly reduced padding */
            text-align: center;
            font-size: 9px; /* Reduced font size */
        }
        .calendar-table th {
            background-color: #f2f2f2;
            font-size: 9px;  /* Reduced font size in table headers */
        }
          .calendar-month {
            text-align: center;
            font-weight: bold;
            margin-bottom: 2px; /* Space between month name and calendar */
            font-size: 10px;     /*Consistent Font Size*/
        }

        .calendar-table td.current-month {
            color: black;
        }
        .calendar-table td.other-month {
            color: #999;
        }
        .calendar-table td.dragged-day {
            background-color: #007bff;
            color: white;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mcButton = document.querySelector('.milestone-button');
            const container = document.querySelector('.front-layer');
            const infoDialog = document.getElementById('info-dialog');
            const closeDialogButton = document.getElementById('close-dialog');
            const dialogDraggedDate = document.getElementById('dialog-dragged-date');
            const draggedDayCircles = document.querySelectorAll('#dragged-days-container .day-circle');
            const buttonDateDisplay = mcButton.querySelector('.date-display');
            const calendarContainer = document.getElementById('calendar-container'); // Calendar container

            // Initialize with a specific date
            const initialDraggedDate = new Date(2025, 10, 16); // November 16, 2025
            const timelineStartDate = new Date(2024, 11, 30); // December 30, 2024
            let draggedDate = initialDraggedDate;

            // Calculate initial position
            function setInitialButtonPosition() {
                const containerRect = container.getBoundingClientRect();
                const cellWidth = containerRect.width / 53;

                // Calculate weeks between timeline start and initial date
                const timeDiff = initialDraggedDate.getTime() - timelineStartDate.getTime();
                const daysDiff = timeDiff / (1000 * 60 * 60 * 24);
                const weeksDiff = Math.floor(daysDiff / 7);

                // Set position to start of week cell
                const initialLeft = weeksDiff * cellWidth;
                mcButton.style.left = `${initialLeft}px`;
            }

            // Initialize button
            setInitialButtonPosition();
            buttonDateDisplay.textContent = String(initialDraggedDate.getDate()).padStart(2, '0');
            mcButton.dataset.draggedDate = formatDate(initialDraggedDate);

            let isDragging = false;
            let startX;
            let startLeft;
            let containerScrollX = 0;
            let finalDate = null; //Keep this declaration

            // Function to generate the calendar
             function generateCalendar(date) {
                const year = date.getFullYear();
                const month = date.getMonth();
                const monthNames = ["January", "February", "March", "April", "May", "June",
                                    "July", "August", "September", "October", "November", "December"];
                const monthName = monthNames[month];

                const firstDayOfMonth = new Date(year, month, 1);
                const lastDayOfMonth = new Date(year, month + 1, 0);
                let firstDayOfWeek = firstDayOfMonth.getDay(); // 0 (Sun), 1 (Mon), ..., 6 (Sat)
                const daysInMonth = lastDayOfMonth.getDate();

                // Adjust firstDayOfWeek to start on Monday (0 becomes 6, others shift down)
                firstDayOfWeek = (firstDayOfWeek === 0) ? 6 : firstDayOfWeek - 1;


                let calendarHTML = `<div class="calendar-month">${monthName} ${year}</div>`; // Add month and year
                calendarHTML += '<table class="calendar-table">';
                calendarHTML += '<thead><tr><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th></tr></thead>';  // Changed header order
                calendarHTML += '<tbody><tr>';


              // Add empty cells for days before the first day of the month
              for (let i = 0; i < firstDayOfWeek; i++) {
                  calendarHTML += '<td></td>';
              }

              // Add cells for each day of the month
              let dayOfMonth = 1;
               for (let i = firstDayOfWeek; i < 7; i++) {
                    const isDraggedDay = dayOfMonth === draggedDate.getDate() && month === draggedDate.getMonth() && year === draggedDate.getFullYear();
                    calendarHTML += `<td class="current-month ${isDraggedDay ? 'dragged-day' : ''}">${dayOfMonth}</td>`;
                    dayOfMonth++;
                }
                calendarHTML += '</tr>';

                // Add the rest of the month
                while (dayOfMonth <= daysInMonth) {
                    calendarHTML += '<tr>';
                    for (let i = 0; i < 7; i++) {
                        if (dayOfMonth <= daysInMonth) {
                            const isDraggedDay = dayOfMonth === draggedDate.getDate() && month === draggedDate.getMonth() && year === draggedDate.getFullYear();
                            calendarHTML += `<td class="current-month ${isDraggedDay ? 'dragged-day' : ''}">${dayOfMonth}</td>`;
                            dayOfMonth++;
                        } else {
                            calendarHTML += '<td></td>'; // Fill with empty cells if needed
                        }
                    }
                    calendarHTML += '</tr>';
                }


                calendarHTML += '</tbody></table>';
                return calendarHTML;
            }


            // Rest of your functions remain the same
            function getDayIndex(date) {
                const day = date.getDay();
                return day === 0 ? 6 : day - 1;
            }

            function getWeekNumber(date) {
                const startOfYear = new Date(date.getFullYear(), 0, 1);
                const pastDays = (date - startOfYear) / (1000 * 60 * 60 * 24);
                return Math.ceil((pastDays + startOfYear.getDay() + 1) / 7);
            }

            function formatDate(date) {
                const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
                const dayOfWeek = days[date.getDay()];
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${dayOfWeek} ${month}/${day}/${year}`;
            }

            function updateButtonDate() {
                if (draggedDate) {
                    const day = String(draggedDate.getDate()).padStart(2, '0');
                    buttonDateDisplay.textContent = day;
                }
            }

            function updateDraggedDayCircles() {
                if (!draggedDate) return;

                const dayIndex = getDayIndex(draggedDate);
                draggedDayCircles.forEach((circle, index) => {
                    if (index === dayIndex) {
                        circle.classList.add('active');
                    } else {
                        circle.classList.remove('active');
                    }
                });
            }

            function showDialog() {
                const buttonRect = mcButton.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const cellWidth = containerRect.width / 53;
                const buttonLeft = mcButton.offsetLeft - containerScrollX;
                const cellNumber = Math.floor(buttonLeft / cellWidth) + 1;

                // Calculate dragged date based on position when dragging
                if (isDragging) {
                    const positionInCell = buttonLeft % cellWidth;
                    const dayIndex = Math.floor((positionInCell / cellWidth) * 7);
                    const startDate = new Date(2024, 11, 30);
                    const daysToAdd = (cellNumber - 1) * 7 + dayIndex;
                    const currentDate = new Date(startDate);
                    currentDate.setDate(startDate.getDate() + daysToAdd);

                    draggedDate = currentDate;
                    mcButton.dataset.draggedDate = formatDate(draggedDate);
                    updateButtonDate();
                }

                // Update dialog with current draggedDate
                dialogDraggedDate.textContent = formatDate(draggedDate);
                updateDraggedDayCircles();

              // Generate and display calendar
                calendarContainer.innerHTML = generateCalendar(draggedDate);


                // Calculate final date
                const startDate = new Date(2024, 11, 30);
                const weekStartDate = new Date(startDate);
                weekStartDate.setDate(startDate.getDate() + (cellNumber - 1) * 7);

                finalDate = weekStartDate; // Keep this line to calculate finalDate
                mcButton.dataset.finalDate = formatDate(finalDate);  //Keep this to store final date

                infoDialog.classList.remove('hidden');
                infoDialog.style.top = `${buttonRect.top - infoDialog.offsetHeight}px`;
                infoDialog.style.left = `${buttonRect.left}px`;
            }

            function onDragEnd() {
                if (isDragging) {
                    isDragging = false;
                    mcButton.style.cursor = 'grab';
                    snapToWeek();

                    // Keep the draggedDate unchanged after snapping
                    dialogDraggedDate.textContent = formatDate(draggedDate);
                    updateDraggedDayCircles();
                    updateButtonDate();

                    // Regenerate the calendar
                    calendarContainer.innerHTML = generateCalendar(draggedDate);

                    showDialog();
                }
            }

            function hideDialog() {
                infoDialog.classList.add('hidden');
            }

            function snapToWeek() {
                const containerRect = container.getBoundingClientRect();
                const cellWidth = containerRect.width / 53;
                const buttonLeft = mcButton.offsetLeft - containerScrollX;
                const cellNumber = Math.floor(buttonLeft / cellWidth) + 1;
                const targetLeft = (cellNumber - 1) * cellWidth + containerScrollX;
                mcButton.style.left = `${targetLeft}px`;
            }

            mcButton.addEventListener('dragstart', function(event) {
                event.preventDefault();
                isDragging = true;
                startX = event.clientX;
                startLeft = mcButton.offsetLeft;
                mcButton.style.cursor = 'grabbing';
                showDialog();
            });

            document.addEventListener('mousemove', function(event) {
                if (!isDragging) return;

                const containerRect = container.getBoundingClientRect();
                const buttonRect = mcButton.getBoundingClientRect();
                let newX = event.clientX - startX + startLeft;
                const minX = 0;
                const maxX = container.offsetWidth - buttonRect.width;
                newX = Math.max(minX, Math.min(newX, maxX));
                mcButton.style.left = newX + 'px';

                const scrollSpeed = 10;
                const scrollThreshold = 50;

                if (event.clientX < scrollThreshold) {
                    containerScrollX = Math.max(0, containerScrollX - scrollSpeed);
                    container.parentElement.style.left = `-${containerScrollX}px`;
                } else if (event.clientX > window.innerWidth - scrollThreshold) {
                    const maxScrollX = container.offsetWidth - window.innerWidth;
                    containerScrollX = Math.min(maxScrollX, containerScrollX + scrollSpeed);
                    container.parentElement.style.left = `-${containerScrollX}px`;
                }
                showDialog();
            });

            document.addEventListener('mouseup', onDragEnd);

            mcButton.addEventListener('mousedown', function(event) {
                event.stopPropagation();
            });
            mcButton.addEventListener('mouseenter', showDialog);
            mcButton.addEventListener('mouseleave', hideDialog);
            mcButton.addEventListener('drag', hideDialog);

            closeDialogButton.addEventListener('click', hideDialog);
        });
    </script>
</x-layout>