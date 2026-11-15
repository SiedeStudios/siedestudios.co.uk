import { ref } from 'vue';

/** Minimal HTML5 drag-and-drop list ordering. */
export function useDragSort(items, onChange) {
    const dragging = ref(null);
    const over = ref(null);

    const start = (index, e) => {
        dragging.value = index;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(index));
    };
    const enter = (index) => (over.value = index);
    const end = () => {
        dragging.value = null;
        over.value = null;
    };
    const drop = (index) => {
        const from = dragging.value;
        end();
        if (from === null || from === index) return;
        const list = [...items.value];
        const [moved] = list.splice(from, 1);
        list.splice(index, 0, moved);
        items.value = list;
        onChange?.(list);
    };

    return { dragging, over, start, enter, end, drop };
}
