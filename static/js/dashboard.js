// dashboard.js - Versão corrigida para imagens adicionais

let currentTab = 'produtos';
let currentProductId = null;
let currentServiceId = null;
let currentAdditionalImage = null;

// Exibir aba selecionada
function showTab(tab) {
    currentTab = tab;

    // Atualizar botões ativos
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.tab-button[onclick="showTab('${tab}')"]`).classList.add('active');

    // Atualizar conteúdo visível
    document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
    document.getElementById(`${tab}-content`).style.display = 'block';

    // Atualizar URL
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);
}

// Função para editar item
function editarItem(tipo, id) {
    const modal = tipo === 'produto' ? 'modalEdicaoProduto' : 'modalEdicaoServico';
    const formId = tipo === 'produto' ? 'formEdicaoProduto' : 'formEdicaoServico';
    const listId = tipo === 'produto' ? 'specs-list' : 'features-list';

    // Limpar campos dinâmicos
    if(document.getElementById(listId)){
        document.getElementById(listId).innerHTML = '';
    }

    // Buscar dados do item
    fetch(`/admin/${tipo}s/${id}`)
        .then(response => {
            if (!response.ok) throw new Error('Erro ao carregar dados do item.');
            return response.json();
        })
        .then(data => {
            console.log('Dados carregados:', data);
            
            if (tipo === 'produto') {
                preencherCamposProduto(data);
                currentProductId = id;
            } else {
                preencherCamposServico(data);
                currentServiceId = id;
            }

            // Configurar ação do formulário
            document.getElementById(formId).action = `/admin/${tipo}s/editar/${id}`;
            document.getElementById(formId).method = 'POST';
            
            // Mostrar modal
            document.getElementById(modal).style.display = 'block';
        })
        .catch(error => {
            console.error(error);
            alert('Erro ao carregar os dados: ' + error.message);
        });
}

// Preencher campos de produtos
function preencherCamposProduto(data) {
    document.getElementById('nomeProduto').value = data.name || '';
    document.getElementById('descricaoProduto').value = data.description || '';
    document.getElementById('categoriaProduto').value = data.category || 'DMR';

    // Limpar especificações anteriores
    const specsContainer = document.getElementById('specs-list');
    specsContainer.innerHTML = '';

    // Processar especificações
    if (data.specs) {
        let specs = [];
        
        if (typeof data.specs === 'string') {
            try {
                specs = JSON.parse(data.specs);
            } catch (e) {
                specs = [data.specs];
            }
        } else if (Array.isArray(data.specs)) {
            specs = data.specs;
        }

        if (specs.length > 0) {
            specs.forEach(spec => {
                if (spec && spec.trim()) {
                    adicionarSpec(spec.trim());
                }
            });
        } else {
            adicionarSpec('');
        }
    } else {
        adicionarSpec('');
    }

    // LIMPAR CAMPO DE IMAGENS ADICIONAIS - MUITO IMPORTANTE
    const imagesProdutoField = document.getElementById('imagesProduto');
    if (imagesProdutoField) {
        imagesProdutoField.value = '';
        console.log('Campo de imagens adicionais limpo');
    }
}

// Preencher campos de serviços
function preencherCamposServico(data) {
    document.getElementById('nomeServico').value = data.name || '';
    document.getElementById('descricaoServico').value = data.description || '';
    document.getElementById('categoriaServico').value = data.category || 'locacao';

    // Limpar características anteriores
    const featuresContainer = document.getElementById('features-list');
    featuresContainer.innerHTML = '';

    // Processar características
    if (data.features) {
        let features = [];
        
        if (typeof data.features === 'string') {
            try {
                features = JSON.parse(data.features);
            } catch (e) {
                features = [data.features];
            }
        } else if (Array.isArray(data.features)) {
            features = data.features;
        }

        if (features.length > 0) {
            features.forEach(feature => {
                if (feature && feature.trim()) {
                    adicionarFeature(feature.trim());
                }
            });
        } else {
            adicionarFeature('');
        }
    } else {
        adicionarFeature('');
    }
}

// Adicionar especificação para produtos
function adicionarSpec(valor = '') {
    const container = document.getElementById('specs-list');
    const div = document.createElement('div');
    div.style.marginBottom = '10px';
    div.innerHTML = `
        <input type="text" name="specs[]" value="${escapeHtml(valor)}" class="form-control" required placeholder="Especificação">
        <button type="button" class="button button-danger" onclick="this.parentElement.remove()">Excluir</button>
    `;
    container.appendChild(div);
}

// Adicionar característica para serviços
function adicionarFeature(valor = '') {
    const container = document.getElementById('features-list');
    const div = document.createElement('div');
    div.style.marginBottom = '10px';
    div.innerHTML = `
        <input type="text" name="features[]" value="${escapeHtml(valor)}" class="form-control" required placeholder="Característica">
        <button type="button" class="button button-danger" onclick="this.parentElement.remove()">Excluir</button>
    `;
    container.appendChild(div);
}

// Função para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Fechar modais
function fecharModalProduto() {
    document.getElementById('modalEdicaoProduto').style.display = 'none';
    document.getElementById('formEdicaoProduto').reset();
    document.getElementById('specs-list').innerHTML = '';
    
    // LIMPAR CAMPOS DE ARQUIVO EXPLICITAMENTE
    const imageField = document.getElementById('imagemProduto');
    const imagesField = document.getElementById('imagesProduto');
    const pdfField = document.getElementById('pdfProduto');
    
    if (imageField) imageField.value = '';
    if (imagesField) imagesField.value = '';
    if (pdfField) pdfField.value = '';
    
    currentProductId = null;
    console.log('Modal de produto fechado e campos limpos');
}

function fecharModalServico() {
    document.getElementById('modalEdicaoServico').style.display = 'none';
    document.getElementById('formEdicaoServico').reset();
    document.getElementById('features-list').innerHTML = '';
    
    // LIMPAR CAMPO DE ARQUIVO
    const imageField = document.getElementById('imagemServico');
    if (imageField) imageField.value = '';
    
    currentServiceId = null;
}

// EXCLUSÃO DE IMAGEM PRINCIPAL
function abrirModalExcluirImagem(productId) {
    currentProductId = productId;
    document.getElementById('modalExcluirImagem').style.display = 'block';
}

function fecharModalExcluirImagem() {
    document.getElementById('modalExcluirImagem').style.display = 'none';
    currentProductId = null;
}

function confirmarExcluirImagem() {
    if (!currentProductId) return;

    fetch(`/admin/produtos/excluir-imagem/${currentProductId}`, {
        method: 'POST'
    })
    .then(response => {
        fecharModalExcluirImagem();
        window.location.reload();
    })
    .catch(error => {
        console.error('Erro:', error);
        fecharModalExcluirImagem();
        window.location.reload();
    });
}

// EXCLUSÃO DE IMAGEM ADICIONAL
function abrirModalExcluirImagemAdicional(productId, imagePath) {
    currentProductId = productId;
    currentAdditionalImage = imagePath;
    document.getElementById('modalExcluirImagemAdicional').style.display = 'block';
}

function fecharModalExcluirImagemAdicional() {
    document.getElementById('modalExcluirImagemAdicional').style.display = 'none';
    currentProductId = null;
    currentAdditionalImage = null;
}

function confirmarExcluirImagemAdicional() {
    if (!currentProductId || !currentAdditionalImage) return;

    // Fazer a requisição para o servidor
    fetch(`/admin/produtos/excluir-imagem-adicional/${currentProductId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            image_path: currentAdditionalImage
        })
    })
    .then(response => {
        fecharModalExcluirImagemAdicional();

        // Encontrar e remover o container da imagem APÓS a requisição ser bem-sucedida
        const imageContainers = document.querySelectorAll('.additional-image-container');
        let containerParaRemover = null;

        imageContainers.forEach(container => {
            const img = container.querySelector('img');
            if (img && img.src.includes(currentAdditionalImage)) {
                containerParaRemover = container;
                container.remove(); // Remove APÓS a requisição
            }
        });

        // Verificar se ainda sobram imagens adicionais
        setTimeout(() => {
            const imagensRestantes = document.querySelectorAll('.additional-image-container');
            if (imagensRestantes.length === 0) {
                // Se não há mais imagens, ocultar todas as seções de imagens adicionais
                document.querySelectorAll('.additional-images').forEach(section => {
                    section.style.display = 'none';
                });
            }
        }, 100);

        // Não recarregar imediatamente para evitar piscar
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    })
    .catch(error => {
        console.error('Erro:', error);
        fecharModalExcluirImagemAdicional();
        // Se deu erro, recarregar para restaurar o estado
        window.location.reload();
    });
}

// EXCLUSÃO DE PDF
function abrirModalExcluirPdf(productId) {
    currentProductId = productId;
    document.getElementById('modalExcluirPdf').style.display = 'block';
}

function fecharModalExcluirPdf() {
    document.getElementById('modalExcluirPdf').style.display = 'none';
    currentProductId = null;
}

function confirmarExcluirPdf() {
    if (!currentProductId) return;

    fetch(`/admin/produtos/excluir-pdf/${currentProductId}`, {
        method: 'POST'
    })
    .then(response => {
        fecharModalExcluirPdf();
        window.location.reload();
    })
    .catch(error => {
        console.error('Erro:', error);
        fecharModalExcluirPdf();
        window.location.reload();
    });
}

// FUNÇÃO PRINCIPAL PARA LIMPAR IMAGENS QUEBRADAS
function limparImagensQuebradas() {
    console.log('Iniciando limpeza de imagens quebradas...');
    
    // Encontrar todas as imagens adicionais
    const imagensAdicionais = document.querySelectorAll('.additional-image-container img');
    let imagensParaRemover = [];
    let totalImagens = imagensAdicionais.length;
    let imagensVerificadas = 0;

    if (totalImagens === 0) {
        console.log('Nenhuma imagem adicional encontrada');
        return;
    }

    imagensAdicionais.forEach((img, index) => {
        // Criar uma nova imagem para testar se carrega
        const testImg = new Image();
        
        testImg.onload = function() {
            imagensVerificadas++;
            console.log(`Imagem ${index + 1}/${totalImagens} OK:`, img.src);
            
            // Se todas as imagens foram verificadas, finalizar limpeza
            if (imagensVerificadas === totalImagens) {
                finalizarLimpeza(imagensParaRemover);
            }
        };
        
        testImg.onerror = function() {
            imagensVerificadas++;
            console.log(`Imagem ${index + 1}/${totalImagens} QUEBRADA:`, img.src);
            
            // Marcar para remoção
            imagensParaRemover.push(img.closest('.additional-image-container'));
            
            // Se todas as imagens foram verificadas, finalizar limpeza
            if (imagensVerificadas === totalImagens) {
                finalizarLimpeza(imagensParaRemover);
            }
        };
        
        testImg.src = img.src;
    });
}

function finalizarLimpeza(imagensParaRemover) {
    console.log(`Removendo ${imagensParaRemover.length} imagens quebradas...`);
    
    // Remover todas as imagens quebradas
    imagensParaRemover.forEach(container => {
        if (container && container.parentNode) {
            container.remove();
        }
    });

    // Verificar se ainda existem imagens adicionais em cada seção
    document.querySelectorAll('.additional-images').forEach(section => {
        const imagensRestantes = section.querySelectorAll('.additional-image-container');
        if (imagensRestantes.length === 0) {
            console.log('Ocultando seção de imagens adicionais vazia');
            section.style.display = 'none';
        }
    });

    console.log('Limpeza concluída');
}

// FUNÇÃO PARA OCULTAR SEÇÕES VAZIAS DE IMAGENS
function ocultarSecoesVazias() {
    document.querySelectorAll('.additional-images').forEach(section => {
        const imagensVisiveis = section.querySelectorAll('.additional-image-container');
        if (imagensVisiveis.length === 0) {
            section.style.display = 'none';
        }
    });
}

// INTERCEPTAR SUBMIT DOS FORMULÁRIOS PARA DEBUG
function interceptarSubmitFormularios() {
    const formProduto = document.getElementById('formEdicaoProduto');
    if (formProduto) {
        formProduto.addEventListener('submit', function(e) {
            console.log('Formulário de produto sendo enviado...');
            
            // Verificar arquivos de imagem
            const imagesField = document.getElementById('imagesProduto');
            if (imagesField && imagesField.files.length > 0) {
                console.log('Imagens adicionais selecionadas:', imagesField.files.length);
                for (let i = 0; i < imagesField.files.length; i++) {
                    console.log(`Arquivo ${i + 1}: ${imagesField.files[i].name} (${imagesField.files[i].size} bytes)`);
                }
            } else {
                console.log('Nenhuma imagem adicional selecionada');
            }

            // Verificar FormData
            const formData = new FormData(formProduto);
            console.log('Dados do formulário:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(`${key}: ${value.name} (${value.size} bytes)`);
                } else {
                    console.log(`${key}: ${value}`);
                }
            }
        });
    }
}

// Inicializar página
window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'produtos';
    showTab(tab);

    // Aguardar um pouco para a página carregar completamente
    setTimeout(() => {
        // Primeira limpeza após 500ms
        limparImagensQuebradas();
        
        // Segunda verificação após 2 segundos para garantir
        setTimeout(() => {
            ocultarSecoesVazias();
        }, 2000);
    }, 500);

    // Adicionar event listeners aos botões de editar
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const tipo = this.dataset.tipo;
            const id = this.dataset.id;
            editarItem(tipo, id);
        });
    });

    // Interceptar submits para debug
    interceptarSubmitFormularios();


    // Fechar modais com ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                }
            });
        }
    });

    // Interceptar eventos de erro de imagem para limpeza contínua
    document.addEventListener('error', function(event) {
        if (event.target.tagName === 'IMG' && event.target.closest('.additional-image-container')) {
            console.log('Imagem quebrada detectada, removendo:', event.target.src);
            const container = event.target.closest('.additional-image-container');
            if (container) {
                container.remove();
                
                // Verificar se a seção pai ficou vazia
                setTimeout(() => {
                    ocultarSecoesVazias();
                }, 100);
            }
        }
    }, true);
};