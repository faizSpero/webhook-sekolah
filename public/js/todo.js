(function () {
    const storageKey = 'todo-items';
    const form = document.getElementById('todo-form');
    const input = document.getElementById('todo-input');
    const list = document.getElementById('todo-list');

    if (!form || !input || !list) {
        return;
    }

    const readItems = () => {
        try {
            const value = localStorage.getItem(storageKey);
            const parsed = value ? JSON.parse(value) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            console.warn('Unable to parse to-do items from localStorage.', error);
            return [];
        }
    };

    const writeItems = (items) => {
        try {
            localStorage.setItem(storageKey, JSON.stringify(items));
            return true;
        } catch (error) {
            console.warn('Unable to save to-do items to localStorage.', error);
            return false;
        }
    };

    let items = readItems();

    const render = () => {
        const fragment = document.createDocumentFragment();

        items.forEach((item, index) => {
            const li = document.createElement('li');
            li.className = 'todo-item';

            const label = document.createElement('label');
            label.className = 'todo-label';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = Boolean(item.done);
            checkbox.addEventListener('change', () => {
                items[index].done = checkbox.checked;
                writeItems(items);
                render();
            });

            const text = document.createElement('span');
            text.textContent = item.text;
            if (item.done) {
                text.className = 'done';
            }

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'todo-remove';
            remove.textContent = 'Delete';
            remove.setAttribute('aria-label', `Delete task: ${item.text}`);
            remove.addEventListener('click', () => {
                items = items.filter((_, i) => i !== index);
                writeItems(items);
                render();
            });

            label.appendChild(checkbox);
            label.appendChild(text);
            li.appendChild(label);
            li.appendChild(remove);
            fragment.appendChild(li);
        });

        list.replaceChildren(fragment);
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const text = input.value.trim();

        if (!text) {
            return;
        }

        items.push({ text, done: false });
        writeItems(items);
        input.value = '';
        render();
    });

    render();
})();
