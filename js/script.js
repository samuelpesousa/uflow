
// Exemplo: Validação de formulário no front-end
document.querySelector('form').addEventListener('submit', function (e) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    if (checkboxes.length === 0) {
        alert('Selecione pelo menos uma matéria.');
        e.preventDefault();
    }
});