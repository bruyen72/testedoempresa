// Variável global para prevenir envios duplicados
let isSubmitting = false;
let lastSubmitTime = 0;
const SUBMIT_COOLDOWN = 3000; // 3 segundos entre envios

// Função para enviar email
function sendContactEmail(event) {
    event.preventDefault();
    event.stopPropagation();

    // PROTEÇÃO 1: Verifica se já está enviando
    if (isSubmitting) {
        console.log('Envio já em andamento. Aguarde.');
        return false;
    }

    // PROTEÇÃO 2: Rate limiting - impede envios em menos de 3 segundos
    const now = Date.now();
    if (now - lastSubmitTime < SUBMIT_COOLDOWN) {
        console.log('Aguarde alguns segundos antes de enviar novamente.');
        alert('Por favor, aguarde alguns segundos antes de enviar novamente.');
        return false;
    }

    // PROTEÇÃO 3: Validação dos campos obrigatórios
    const name = document.getElementById('contact_name').value.trim();
    const email = document.getElementById('contact_email').value.trim();
    const message = document.getElementById('contact_message').value.trim();

    if (!name || !email || !message) {
        alert('Por favor, preencha todos os campos obrigatórios.');
        return false;
    }

    const button = document.getElementById('submitButton');
    const buttonText = button.querySelector('.button-text');
    const buttonLoading = button.querySelector('.button-loading');

    // PROTEÇÃO 4: Desabilita botão ANTES de enviar
    isSubmitting = true;
    button.disabled = true;
    buttonText.style.display = 'none';
    buttonLoading.style.display = 'inline-block';

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', document.getElementById('contact_phone').value);
    formData.append('message', message);

    fetch('/enviar-contato-site', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert('Mensagem enviada com sucesso!');
            document.getElementById('contactForm').reset();
            lastSubmitTime = Date.now();
        } else {
            throw new Error(data.error || 'Erro ao enviar mensagem');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao enviar mensagem. Tente novamente.');
    })
    .finally(() => {
        // PROTEÇÃO 5: Reabilita após um delay mínimo
        setTimeout(() => {
            isSubmitting = false;
            button.disabled = false;
            buttonText.style.display = 'inline-block';
            buttonLoading.style.display = 'none';
        }, 1000);
    });

    return false;
}

// Máscara para telefone
document.getElementById('contact_phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    }
    e.target.value = value;
});

        // Menu Toggle
        const mobileToggle = document.querySelector('.mobile-toggle');
        const navMenu = document.querySelector('.nav-menu');

        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    if (navMenu.classList.contains('active')) {
                        navMenu.classList.remove('active');
                    }
                }
            });
        });

        // Animation on Scroll
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate');
                    }
                });
            }, { threshold: 0.1 }
        );

        document.querySelectorAll('.product-card, .contact-form, .footer-section')
            .forEach(el => observer.observe(el));
        document.querySelectorAll('.radio-container img').forEach(img => {
            img.addEventListener('click', (event) => {
                event.preventDefault(); // cancela o comportamento padrão
            });
        });

        // Atualiza o feed do Instagram a cada 5 minutos
        setInterval(() => {
            const instagramIframe = document.querySelector('.instagram-feed iframe');
            if (instagramIframe) {
                instagramIframe.src = instagramIframe.src;
            }
        }, 300000);

        // Script para manipular o placeholder de carregamento do Instagram
        document.addEventListener("DOMContentLoaded", function () {
            const script = document.querySelector('script[src="//www.instagram.com/embed.js"]');
            const placeholder = document.querySelector('.loading-placeholder');
            const instagramMedia = document.querySelector('.instagram-media');

            script.onload = function () {
                if (placeholder) {
                    placeholder.style.display = 'none';
                    if (instagramMedia) {
                        instagramMedia.style.display = 'block';
                    }
                }
            };

            script.onerror = function () {
                if (placeholder) {
                    placeholder.innerHTML = '<p>Não foi possível carregar o feed do Instagram. Tente novamente mais tarde.</p>';
                }
            };
        });