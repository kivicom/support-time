document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('upload-form');
    const typeForm = document.getElementById('daytype-form');
    const reportContainer = document.getElementById('report-container');

    if (uploadForm) {
        handleFormSubmit(uploadForm, '/', (html) => {
            document.open();
            document.write(html);
            document.close();
        });
    }

    if (typeForm) {
        handleFormSubmit(typeForm, '/report', (html) => {
            reportContainer.innerHTML = html;
        });
    }

    function handleFormSubmit(form, url, onSuccess) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const formData = new FormData(form);

                const res = await fetch(`${BASE_PATH}${url}`, {
                    method: 'POST',
                    body: formData
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
