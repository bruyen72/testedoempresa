// add_service.js - Funcionalidades da página de adicionar serviços

// Função para adicionar característica (mantendo código original)
function addFeature() {
    const container = document.getElementById('features-container');
    const featureDiv = document.createElement('div');
    featureDiv.className = 'feature-item';
    featureDiv.innerHTML = `
        <input type="text" name="features[]" class="form-control" 
               placeholder="Digite uma característica importante do serviço" required>
        <button type="button" class="button button-danger" onclick="this.parentElement.remove()">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(featureDiv);
}

// Função para remover característica (caso precise usar com botão onclick)
function removeFeature(button) {
    const container = document.getElementById('features-container');
    
    // Não permitir remover se houver apenas uma característica
    if (container.children.length > 1) {
        button.parentElement.remove();
    } else {
        alert('Pelo menos uma característica é obrigatória.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar validação do formulário
    initializeFormValidation();
    
    // Inicializar preview de imagem
    initializeImagePreview();
});

// Função para validação do formulário
function initializeFormValidation() {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!validateForm()) {
                event.preventDefault();
            }
        });
    }
}

// Função para validar o formulário
function validateForm() {
    const name = document.getElementById('name').value.trim();
    const description = document.getElementById('description').value.trim();
    const category = document.getElementById('category').value;
    const image = document.getElementById('image').files[0];
    const features = document.querySelectorAll('input[name="features[]"]');
    
    // Validar nome
    if (!name) {
        alert('Por favor, digite o nome do serviço.');
        document.getElementById('name').focus();
        return false;
    }
    
    // Validar descrição
    if (!description || description.length < 20) {
        alert('Por favor, digite uma descrição mais detalhada (mínimo 20 caracteres).');
        document.getElementById('description').focus();
        return false;
    }
    
    // Validar categoria
    if (!category) {
        alert('Por favor, selecione uma categoria para o serviço.');
        document.getElementById('category').focus();
        return false;
    }
    
    // Validar imagem
    if (!image) {
        alert('Por favor, selecione uma imagem para o serviço.');
        document.getElementById('image').focus();
        return false;
    }
    
    // Validar tipo e tamanho da imagem
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(image.type)) {
        alert('Por favor, selecione uma imagem válida (JPG, PNG ou WebP).');
        document.getElementById('image').focus();
        return false;
    }
    
    if (image.size > 2 * 1024 * 1024) { // 2MB
        alert('A imagem deve ter no máximo 2MB.');
        document.getElementById('image').focus();
        return false;
    }
    
    // Validar características
    let hasValidFeature = false;
    features.forEach(feature => {
        if (feature.value.trim()) {
            hasValidFeature = true;
        }
    });
    
    if (!hasValidFeature) {
        alert('Por favor, adicione pelo menos uma característica válida.');
        features[0].focus();
        return false;
    }
    
    return true;
}

// Função para preview da imagem
function initializeImagePreview() {
    const imageInput = document.getElementById('image');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            
            if (file) {
                // Criar preview se não existir
                let preview = document.getElementById('image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'image-preview';
                    preview.className = 'image-preview';
                    imageInput.parentNode.appendChild(preview);
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-top: 10px;">
                    `;
                }
                reader.readAsDataURL(file);
            }
        });
    }
}