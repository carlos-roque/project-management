import Sortable from 'sortablejs'; // Import SortableJS
import './bootstrap'; // Ensure this is included if you have other dependencies


// Initialize Sortable on the grid
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('grid');
    new Sortable(grid, {
        animation: 150,
        swap: true,  // Enable swapping items
        draggable: '.grid-cell',  // Make each cell draggable
        onEnd: function (evt) {
            console.log('Item moved:', evt.item);
        }
    });
});