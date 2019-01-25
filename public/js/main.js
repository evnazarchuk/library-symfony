const articles = document.getElementById('libery');

if (articles) {
    articles.addEventListener('click', e => {
        if (e.target.className === 'btn btn-danger delete-libery') {
            if (confirm('Are you sure?')) {
                const id = e.target.getAttribute('data-id');

                fetch(`/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }
        }
    });
}