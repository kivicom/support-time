document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('upload-form');
    const typeForm = document.getElementById('daytype-form');
    const reportContainer = document.getElementById('report-container');

    // Убедимся, что BASE_PATH определён
    const BASE_PATH = window.BASE_PATH || '/support-time';

    if (uploadForm) {

        handleFormSubmit(uploadForm, 'upload', (html) => {
            document.open();
            document.write(html);
            document.close();
        });
    }

    if (typeForm) {
        handleFormSubmit(typeForm, 'report', (html) => {
            reportContainer.innerHTML = html;
        });
    }

    function handleFormSubmit(form, action, onSuccess) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const formData = new FormData(form);
                formData.append('action', action); // Добавляем параметр action

                const res = await fetch(`${BASE_PATH}/`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Connection': 'keep-alive' // Для избежания HTTP/2 ошибок
                    }
                });

                if (!res.ok) throw new Error(`HTTP ${res.status}`);

                const result = await res.text();
                onSuccess(result);
            } catch (err) {
                console.error('Ошибка при отправке формы:', err);
                alert('Ошибка при отправке запроса. Попробуйте позже.');
            }
        });
    }
});