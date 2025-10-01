// add_product.js - Funcionalidades da página de adicionar produtos (mantendo código existente)

// Função para adicionar especificação (mantendo o código original)
function addSpec() {
    const container = document.getElementById('specsContainer');
    const specItem = document.createElement('div');
    specItem.className = 'spec-item';
    specItem.innerHTML = `
        <input type="text" name="spec[]" placeholder="Ex: GPS Integrado" required>
        <button type="button" class="remove-spec" onclick="removeSpec(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(specItem);
}

// Função para remover especificação (mantendo o código original)
function removeSpec(button) {
    const container = document.getElementById('specsContainer');
    if (container.children.length > 1) {
        button.parentElement.remove();
    }
}

// Função para preview da imagem (mantendo o código original)
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

// Adicionar validação e melhorias
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar validação do formulário
    initializeFormValidation();
    
    // Inicializar preview de múltiplas imagens
    initializeMultipleImagePreview();
});

// Função para validação do formulário
function initializeFormValidation() {
    const form = document.querySelector('.product-form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (validateProductForm() == false) {
                event.preventDefault();
            }
        });
    }
}

// Função para validar o formulário de produto
function validateProductForm() {
//     const name = document.getElementById('name').value.trim();
//     const category = document.getElementById('category').value;
//     const description = document.getElementById('description').value.trim();
//     const image = document.getElementById('image').files[0];
//     const specs = document.querySelectorAll('input[name="spec[]"]');
    
    // Validar nome
    if (!name) {
        alert('Por favor, digite o nome do produto.');
        document.getElementById('name').focus();
        return false;
    }
    
//     // Validar categoria
    if (!category) {
        alert('Por favor, selecione uma categoria para o produto.');
        document.getElementById('category').focus();
        return false;
    }

    // Validar descrição
    if (!description || description.length < 20) {
        alert('Por favor, digite uma descrição mais detalhada (mínimo 20 caracteres).');
        document.getElementById('description').focus();
        return false;
    }
    
    // Validar imagem principal
    if (!image) {
        alert('Por favor, selecione uma imagem principal para o produto.');
        document.getElementById('image').focus();
        return false;
    }
    
    // Validar tipo e tamanho da imagem
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(image.type)) {
        alert('Por favor, selecione uma imagem válida (JPG, JPEG ou PNG).');
        document.getElementById('image').focus();
        return false;
    }
    
    if (image.size > 50 * 1024 * 1024) { // 50MB
        alert('A imagem deve ter no máximo 50MB.');
        document.getElementById('image').focus();
        return false;
    }
    
    // Validar especificações
    let hasValidSpec = false;
    specs.forEach(spec => {
        if (spec.value.trim()) {
            hasValidSpec = true;
        }
    });
    
    if (!hasValidSpec) {
        alert('Por favor, adicione pelo menos uma especificação válida.');
        specs[0].focus();
        return false;
    }
    
    return true;
}

// Função para preview de múltiplas imagens
function initializeMultipleImagePreview() {
    const multipleImagesInput = document.getElementById('images');
    
    if (multipleImagesInput) {
        multipleImagesInput.addEventListener('change', function(event) {
            const files = event.target.files;
            
            // Remover preview anterior se existir
            const existingPreview = document.getElementById('multiple-images-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            if (files.length > 0) {
                // Criar container para preview
                const previewContainer = document.createElement('div');
                previewContainer.id = 'multiple-images-preview';
                previewContainer.style.cssText = 'display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;';
                
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgContainer = document.createElement('div');
                            imgContainer.style.cssText = 'position: relative; width: 100px; height: 100px;';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;';
                            img.alt = `Preview ${index + 1}`;
                            
                            imgContainer.appendChild(img);
                            previewContainer.appendChild(imgContainer);
                        }
                        reader.readAsDataURL(file);
                    }
                });
                
                multipleImagesInput.parentNode.appendChild(previewContainer);
            }
        });
    }
}

// Função para validar PDF
function validatePDF(input) {
    const file = input.files[0];
    
    if (file) {
        if (file.type !== 'application/pdf') {
            alert('Por favor, selecione apenas arquivos PDF.');
            input.value = '';
            return false;
        }
        
        if (file.size > 50 * 1024 * 1024) { // 50MB
            alert('O arquivo PDF deve ter no máximo 50MB.');
            input.value = '';
            return false;
        }
    }
    
    return true;
}

// Adicionar validação para o campo PDF se existir
document.addEventListener('DOMContentLoaded', function() {
    const pdfInput = document.getElementById('pdf');
    if (pdfInput) {
        pdfInput.addEventListener('change', function() {
            validatePDF(this);
        });
    }
});