// KANBAN JavaScript - v2026-05-02-EXT-FINAL
console.log('Loading EXT KANBAN v2026-05-02-EXT-FINAL');

function allowDrop(ev) {
    ev.preventDefault();
}

function dragEnter(ev) {
    ev.preventDefault();
    var col = ev.target.closest('.kanban-column');
    if (col) col.classList.add('kanban-drag-over');
}

function dragLeave(ev) {
    var col = ev.target.closest('.kanban-column');
    if (col) col.classList.remove('kanban-drag-over');
}

function drag(ev, dealId) {
    window.draggedDealId = dealId;
    ev.dataTransfer.setData('text/plain', dealId);
    ev.target.style.opacity = '0.5';
}

function drop(ev, stageId) {
    ev.preventDefault();
    var col = ev.target.closest('.kanban-column');
    if (!col) return;
    col.classList.remove('kanban-drag-over');

    var dealId = window.draggedDealId;
    if (!dealId) {
        console.error('No deal ID!');
        return;
    }

    var card = document.getElementById('deal-' + dealId);
    if (card) {
        card.style.opacity = '1';
        card.classList.add('updating');
        col.appendChild(card);
    }

    updateColumnCounts();

    var formData = new FormData();
    formData.append('deal_id', dealId);
    formData.append('stage_id', stageId);

    fetch('api/kanban.php?v=' + Date.now(), {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        console.log('Server response:', data);
        if (card) {
            card.classList.remove('updating');
            if (data.success) {
                card.classList.add('flash-success');
                setTimeout(function() { card.classList.remove('flash-success'); }, 1000);
                console.log('✅ Deal updated: affected=' + data.affected);
            } else {
                console.error('❌ Error:', data.error);
            }
        }
    })
    .catch(function(err) {
        console.error('Network error:', err);
        if (card) card.classList.remove('updating');
    });
}

function updateColumnCounts() {
    console.log('EXT: Updating column counts...');
    document.querySelectorAll('.kanban-column').forEach(function(col) {
        var parent = col.parentElement;
        var countEl = parent.querySelector('.text-xs.bg-slate-100');
        if (countEl) {
            var cards = col.querySelectorAll('.kanban-card');
            console.log('Column has', cards.length, 'cards');
            countEl.textContent = cards.length;
        } else {
            console.log('Count element not found');
        }
    });
}

// Add dragend listeners when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.kanban-card').forEach(function(card) {
            card.addEventListener('dragend', function() {
                card.style.opacity = '1';
            });
        });
    });
} else {
    document.querySelectorAll('.kanban-card').forEach(function(card) {
        card.addEventListener('dragend', function() {
            card.style.opacity = '1';
        });
    });
}

console.log('EXT Kanban loaded');