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
    },
    { threshold: 0.1 }
);

// Função para observar animações
function initAnimations() {
    document.querySelectorAll('.solucao-card')
        .forEach(el => observer.observe(el));
}

// Função para truncar texto
function truncateText(text, maxLength = 120) {
    if (!text || text.length <= maxLength) return text;
    
    const cleanText = text.replace(/\s+/g, ' ').trim();
    if (cleanText.length <= maxLength) return cleanText;
    
    const truncated = cleanText.substring(0, maxLength);
    const lastSpace = truncated.lastIndexOf(' ');
    
    if (lastSpace > 0) {
        return cleanText.substring(0, lastSpace) + '...';
    }
    
    return truncated + '...';
}

// Função para limpar texto
function cleanText(text) {
    if (!text) return '';
    return text
        .replace(/\s+/g, ' ')
        .replace(/\n\s*\n/g, '\n\n')
        .trim();
}

// Função para obter nome da categoria
function getCategoryName(category) {
    const categoryNames = {
        'locacao': 'Locação de Equipamentos',
        'manutencao': 'Manutenção de Equipamentos',
        'projetos': 'Projetos Técnicos',
        'legalizacao': 'Legalização ANATEL',
        'implantacao': 'Implantação de Sistemas',
        'consultoria': 'Consultoria Técnica',
        'treinamento': 'Treinamento e Capacitação'
    };
    return categoryNames[category] || category.charAt(0).toUpperCase() + category.slice(1);
}

// FUNÇÃO PRINCIPAL - Carregar serviços
function carregarServicos() {
    const grid = document.getElementById('servicosGrid');
    
    // Timeout para remover loading se demorar muito
    const loadingTimeout = setTimeout(() => {
        if (grid.querySelector('.loading')) {
            grid.innerHTML = `
                <div class="error-services">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; color: #ff6b6b;"></i>
                    <h3>Tempo Limite Excedido</h3>
                    <p>A requisição demorou muito para responder.<br>
                    <button onclick="carregarServicos()">
                        <i class="fas fa-redo"></i> Tentar Novamente
                    </button></p>
                </div>
            `;
        }
    }, 10000); // 10 segundos

    fetch('/api/servicos')
        .then(response => {
            clearTimeout(loadingTimeout);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados recebidos:', data); // Debug
            
            if (data.services && data.services.length > 0) {
                grid.innerHTML = data.services.map((service, index) => {
                    const cleanDescription = cleanText(service.description);
                    const truncatedDescription = truncateText(cleanDescription, 120);
                    const hasLongText = cleanDescription.length > 120;
                    
                    const features = service.features ? 
                        (typeof service.features === 'string' ? 
                            JSON.parse(service.features) : 
                            service.features) : [];
                    
                    const imagePath = service.image_path ? 
                        `/static/uploads/${service.image_path}` : 
                        '/static/servicos/default.png';

                    return `
                        <div class="solucao-card" data-service-id="${service.id}">
                            <div class="solucao-content">
                                <div class="solucao-imagem">
                                    <img src="${imagePath}" 
                                         alt="${service.name}"
                                         onerror="this.src='/static/servicos/default.png'; this.onerror=null;">
                                </div>
                                <div class="solucao-texto">
                                    <h3>${service.name}</h3>
                                    
                                    <div class="servico-descricao">
                                        ${truncatedDescription}
                                    </div>
                                    
                                    ${features.length > 0 ? `
                                        <div class="servico-divider"></div>
                                        <ul class="solucao-features">
                                            ${features.slice(0, 3).map(feature => `
                                                <li>
                                                    <i class="fas fa-check-circle"></i> 
                                                    ${cleanText(feature)}
                                                </li>
                                            `).join('')}
                                            ${features.length > 3 ? `
                                                <li>
                                                    <i class="fas fa-plus-circle"></i> 
                                                    <strong>+ ${features.length - 3} benefício${features.length - 3 > 1 ? 's' : ''} adiciona${features.length - 3 > 1 ? 'is' : 'l'}</strong>
                                                </li>
                                            ` : ''}
                                        </ul>
                                    ` : ''}
                                    
                                    ${service.category ? `
                                        <div class="service-category">
                                            <span>${getCategoryName(service.category)}</span>
                                        </div>
                                    ` : ''}
                                    
                                    <div class="service-actions">
                                        ${hasLongText ? `
                                            <a href="#" class="btn-leia-mais" 
                                               onclick="expandirDescricao(this, \`${cleanDescription.replace(/`/g, '\\`').replace(/\$/g, '\\$')}\`); return false;"
                                               data-expanded="false">
                                                <i class="fas fa-book-open"></i> Leia mais
                                            </a>
                                        ` : ''}
                                        <button onclick="abrirModalContato('${service.category || service.name}')" 
                                                class="button button-primary">
                                            <i class="fas fa-info-circle"></i> Saiba Mais
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                // Iniciar animações
                setTimeout(() => {
                    initAnimations();
                }, 100);
                
            } else {
                grid.innerHTML = `
                    <div class="no-services">
                        <i class="fas fa-tools" style="font-size: 3rem; margin-bottom: 1rem; color: var(--accent);"></i>
                        <h3>Nenhum Serviço Cadastrado</h3>
                        <p>Ainda não há serviços cadastrados no sistema.<br>Em breve teremos novos serviços disponíveis!</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            clearTimeout(loadingTimeout);
            console.error('Erro ao carregar serviços:', error);
            
            // Diferentes tipos de erro
            let errorMessage = 'Erro desconhecido';
            if (error.message.includes('Failed to fetch')) {
                errorMessage = 'Não foi possível conectar ao servidor';
            } else if (error.message.includes('404')) {
                errorMessage = 'API de serviços não encontrada';
            } else if (error.message.includes('500')) {
                errorMessage = 'Erro interno do servidor';
            } else {
                errorMessage = error.message;
            }
            
            grid.innerHTML = `
                <div class="error-services">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; color: #ff6b6b;"></i>
                    <h3>Erro ao Carregar Serviços</h3>
                    <p>${errorMessage}<br>
                    <button onclick="carregarServicos()">
                        <i class="fas fa-redo"></i> Tentar Novamente
                    </button></p>
                </div>
            `;
        });
}

// Função para expandir descrição
function expandirDescricao(element, description) {
    const card = element.closest('.solucao-card');
    const descricaoContainer = card.querySelector('.servico-descricao');
    const isExpanded = element.getAttribute('data-expanded') === 'true';
    
    if (isExpanded) {
        // Contrair
        const truncated = truncateText(description, 120);
        descricaoContainer.innerHTML = truncated;
        element.innerHTML = '<i class="fas fa-book-open"></i> Leia mais';
        element.setAttribute('data-expanded', 'false');
        element.classList.remove('expanded');
    } else {
        // Expandir
        descricaoContainer.innerHTML = `
            <div class="descricao-completa">
                ${description.replace(/\n/g, '<br>')}
            </div>
        `;
        element.innerHTML = '<i class="fas fa-book"></i> Leia menos';
        element.setAttribute('data-expanded', 'true');
        element.classList.add('expanded');
        
        // Scroll suave
        setTimeout(() => {
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }, 300);
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM carregado, iniciando carregamento de serviços...');
    
    // Carregar serviços
    carregarServicos();

    // Formulário de contato
    const formularioContato = document.getElementById('formularioContato');
    if (formularioContato) {
        formularioContato.addEventListener('submit', async function (event) {
            event.preventDefault();

            const dados = {
                nome: document.getElementById('nomeContato').value.trim(),
                email: document.getElementById('emailContato').value.trim(),
                telefone: document.getElementById('telefoneContato').value.trim(),
                categoria: document.getElementById('servicoCategoria').value.trim(),
                mensagem: document.getElementById('mensagemContato').value.trim()
            };

            // Validação
            if (!dados.nome || !dados.email || !dados.mensagem) {
                alert('Os campos Nome, Email e Mensagem são obrigatórios.');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(dados.email)) {
                alert('Por favor, insira um email válido.');
                return;
            }

            // Estado de carregamento
            const botaoEnviar = formularioContato.querySelector('button[type="submit"]');
            const textoOriginal = botaoEnviar.innerHTML;
            botaoEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            botaoEnviar.disabled = true;

            try {
                const response = await fetch('/enviar-serviço', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dados)
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => null);
                    const errorMessage = errorData?.error || 'Erro desconhecido no envio';
                    throw new Error(errorMessage);
                }

                const resultado = await response.json();
                alert(resultado.message || 'Mensagem enviada com sucesso!');
                formularioContato.reset();
                fecharModalContato();
            } catch (erro) {
                console.error('Erro:', erro);
                alert(`Erro ao enviar mensagem: ${erro.message}. Tente novamente.`);
            } finally {
                botaoEnviar.innerHTML = textoOriginal;
                botaoEnviar.disabled = false;
            }
        });
    }

    // Fechar modal com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            fecharModalContato();
        }
    });

    // Fechar modal clicando fora
    window.addEventListener('click', function (event) {
        const modal = document.getElementById('modalContato');
        if (event.target === modal) {
            fecharModalContato();
        }
    });
});

// Funções globais
window.abrirModalContato = function(categoria) {
    const modal = document.getElementById('modalContato');
    const categoriaInput = document.getElementById('servicoCategoria');
    const modalTitle = modal.querySelector('.modal-title');
    
    if (categoriaInput) categoriaInput.value = categoria;
    modalTitle.textContent = `Solicitar Informações - ${getCategoryName(categoria)}`;
    modal.style.display = 'block';
};

window.fecharModalContato = function() {
    const modal = document.getElementById('modalContato');
    if (modal) modal.style.display = 'none';
};

window.expandirDescricao = expandirDescricao;
window.carregarServicos = carregarServicos;

// Debug - verificar se API existe
fetch('/api/servicos')
    .then(response => {
        console.log('Status da API:', response.status);
        if (response.status === 404) {
            console.warn('API /api/servicos não encontrada - verifique se o endpoint existe');
        }
    })
    .catch(error => {
        console.warn('Erro ao testar API:', error.message);
    });