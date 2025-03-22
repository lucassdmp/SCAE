document.addEventListener('DOMContentLoaded', function () {

    const sections = document.querySelectorAll('[data-name^="ff_cn_id_"]');

    sections.forEach(section => {

        const radios = section.querySelectorAll('input[type="radio"]');

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                const selectedValue = this.value;
                const sectionRadios = section.querySelectorAll(`input[type="radio"][value="${selectedValue}"]`);

                sectionRadios.forEach(otherRadio => {
                    if (otherRadio !== this) {
                        otherRadio.disabled = true;
                        otherRadio.checked = false;
                    }
                });

                sections.forEach(otherSection => {
                    if (otherSection !== section) {
                        const otherSectionRadios = otherSection.querySelectorAll(`input[type="radio"][value="${selectedValue}"]`);
                        otherSectionRadios.forEach(otherSectionRadio => {
                            otherSectionRadio.disabled = false;
                        });
                    }
                });
            });
        });
    });

    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    function showSelectAndClickButton(buttonId) {
        const buttons = document.querySelectorAll('.e-n-tab-title');
        buttons.forEach(button => {
            if (button.id === buttonId) {
                button.style.display = 'block';
                button.setAttribute('aria-selected', 'true');
                button.removeAttribute('tabindex');

                // Simula um clique no botão
                setTimeout(() => button.click(), 100);
            } else {
                button.style.display = 'none';
                button.setAttribute('aria-selected', 'false');
                button.setAttribute('tabindex', '-1');
            }
        });
    }

    function redirectToHomePage() {
        window.location.href = '/';
    }

    const formcode = getQueryParam('formcode');

    if (formcode && formcode.startsWith('1d')) {
        showSelectAndClickButton('e-n-tab-title-1189029131');
    } else if (formcode && formcode.startsWith('2d')) {
        showSelectAndClickButton('e-n-tab-title-1189029132');
    } else if (formcode && formcode.startsWith('3d')) {
        showSelectAndClickButton('e-n-tab-title-1189029133');
    }
});


jQuery(document).ready(function ($) {
    // Função para coletar os dados de um formulário específico
    function collectFormData(formId) {
        var formData = {};

        // Coletar o nome (campo com a classe 'username')
        var username = $('#' + formId + ' .username').val();
        if (username) {
            formData['username'] = username;
        }

        // Coletar o email (campo com a classe 'useremail')
        var useremail = $('#' + formId + ' .useremail').val();
        if (useremail) {
            formData['useremail'] = useremail;
        }

        // Iterar sobre os inputs radio do formulário específico
        for (var i = 1; i <= 16; i++) {
            var inputName = 'input_radio_' + i;
            var selectedValue = $('#' + formId + ' input[name="' + inputName + '"]:checked').val();
            if (selectedValue) {
                formData[inputName] = selectedValue;
            }
        }

        return formData;
    }

    // Função para enviar os dados via AJAX
    function sendFormData(formId, callback) {
        var formData = collectFormData(formId);
        console.log(formData); // Para depuração

        $.ajax({
            url: ajaxurl, // ajaxurl é uma variável global fornecida pelo WordPress
            type: 'POST',
            data: {
                action: 'process_form_data', // Ação do WordPress
                form_id: formId, // ID do formulário enviado
                form_data: formData // Dados do formulário
            },
            success: function (response) {
                console.log('Resposta do servidor:', response);
                if (callback && typeof callback === 'function') {
                    callback();
                }
            },
            error: function (error) {
                console.error('Erro ao enviar dados:', error);
            }
        });


    }

    $('.send-8').on('click', function (e) {
        e.preventDefault();
        sendFormData('fluentform_8', function () {
            $('#fluentform_8').submit(); // Submit the form after processing
        });
    });

    $('.send-9').on('click', function (e) {
        e.preventDefault();
        sendFormData('fluentform_9', function () {
            $('#fluentform_9').submit(); // Submit the form after processing
        });
    });

    $('.send-10').on('click', function (e) {
        e.preventDefault();
        sendFormData('fluentform_10', function () {
            $('#fluentform_10').submit(); // Submit the form after processing
        });
    });
});