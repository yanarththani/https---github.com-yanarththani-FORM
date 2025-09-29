<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Form Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 1px solid #eee;
        }

        .header-actions .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            /* margin-left: 10px; */
        }

        .btn-primary { 
            background-color: #007bff; 
            color: #fff; 
        }
        .btn-secondary { 
            background-color: #6c757d; 
            color: #fff; 
        }
        .btn-danger { 
            background-color: #dc3545; 
            color: #fff; 
        }
        .btn-success { 
            background-color: #28a745; 
            color: #fff; 
        }

        main { 
            padding: 30px; 
        }
        h1, h2 { 
            color: #333; 
        }
        .form-settings, .form-fields {
            background: #fdfdfd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
            margin-bottom: 20px;
        }

        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 500; 
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .field-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .field-btn {
            padding: 12px 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: rgb(229 231 235);
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            width: 200px;
        }
        .field-btn:hover { 
            background: #e9e9e9; 
            transform: translateY(-2px); 
        }

        .builder-area {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .field-card {
            background: #fff;
            border: 1px solid #ddd;
            border-left: 5px solid #007bff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
            transition: all 0.2s;
        }

        .field-card:hover { 
            transform: translateY(-3px); 
        }

        .field-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-weight: bold;
            color: #444;
        }

        .field-header .actions button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #888;
            margin-left: 10px;
        }
        .field-header .actions button:hover { 
            color: #dc3545; 
        }

        .field-body .form-group { 
            margin-bottom: 10px; 
        }
        .required-checkbox {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .required-checkbox input { 
            margin-right: 5px; 
        }

        .options-container {
            margin-top: 10px;
        }

        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .option-item input {
            margin-right: 10px;
        }

        .add-option {
            margin-top: 10px;
            padding: 5px 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-active { 
            background: #d4edda; 
            color: #155724; 
        }
        .status-inactive { 
            background: #f8d7da; 
            color: #721c24; 
        }
    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Form Builder</h1>
            <div class="header-actions">
                <button class="btn btn-success" id="save-form">Save Form</button>
                <button class="btn btn-primary" id="preview-form">Preview Form</button>
                <button class="btn btn-secondary" id="manage-forms">Manage Forms</button>
                <button class="btn btn-danger">Logout</button>
            </div>
        </header>

        <main>
            <div class="form-settings">
                <h2>Form Settings</h2>
                <div class="form-group">
                    <label for="form-title">Form Title</label>
                    <input type="text" id="form-title" class="form-control" placeholder="e.g., Contact Us Form">
                </div>
                <div class="form-group">
                    <label for="form-description">Form Description</label>
                    <textarea id="form-description" class="form-control" rows="3" placeholder="A brief description of the form"></textarea>
                </div>
                <div class="form-group">
                    <label for="form-status">Form Status</label>
                    <select id="form-status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-fields">
                <h2>Form Fields</h2>
                <div class="field-options">
                    <button class="field-btn" data-type="text">
                        <i class="fas fa-text-width text-2xl mb-2"></i>Short Text
                    </button>
                    <button class="field-btn" data-type="email">
                        <i class="fas fa-at text-2xl mb-2"></i>Email
                    </button>
                    <button class="field-btn" data-type="textarea">
                        <i class="fas fa-align-left text-2xl mb-2"></i>Long Text
                    </button>
                    <button class="field-btn" data-type="number">
                        <i class="fas fa-hashtag text-2xl mb-2"></i>Number
                    </button>
                    <button class="field-btn" data-type="date">
                        <i class="fas fa-calendar-alt text-2xl mb-2"></i>Date
                    </button>
                    <button class="field-btn" data-type="multiple-choice">
                        <i class="fas fa-dot-circle text-2xl mb-2"></i>Multiple Choice
                    </button>
                    <button class="field-btn" data-type="checkboxes">
                        <i class="fas fa-check-square text-2xl mb-2"></i>Checkboxes
                    </button>
                    <button class="field-btn" data-type="dropdown">
                        <i class="fas fa-caret-square-down text-2xl mb-2"></i>Dropdown
                    </button>
                </div>

                <div id="form-builder-area" class="builder-area">
                    <!-- Fields will be added here dynamically -->
                </div>
            </div>
        </main>
    </div>

    <!-- Forms Management Modal -->
    <div id="forms-modal" class="modal">
        <div class="modal-content">
            <h2>Manage Forms</h2>
            <div id="forms-list"></div>
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
        let fieldCount = 0;
        let currentFormId = null;

        const googleSheetEndpoint = 'https://script.google.com/macros/s/AKfycbyU3v6ow3yDrUSE3ykKI00qy4VngsQyLplqhmzf4FGNU-k6PCe9-KQHqYqvjCY514f-/exec';


        // Field type definitions
        const fieldTypes = {
            'text': { icon: 'fa-text-width', label: 'Short Text' },
            'email': { icon: 'fa-at', label: 'Email' },
            'textarea': { icon: 'fa-align-left', label: 'Long Text' },
            'number': { icon: 'fa-hashtag', label: 'Number' },
            'date': { icon: 'fa-calendar-alt', label: 'Date' },
            'multiple-choice': { icon: 'fa-dot-circle', label: 'Multiple Choice' },
            'checkboxes': { icon: 'fa-check-square', label: 'Checkboxes' },
            'dropdown': { icon: 'fa-caret-square-down', label: 'Dropdown' }
        };

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Add field buttons
            document.querySelectorAll('.field-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    addField(this.dataset.type);
                });
            });

            // Save form button
            document.getElementById('save-form').addEventListener('click', saveForm);

            // Preview form button
            document.getElementById('preview-form').addEventListener('click', previewForm);

            // Manage forms button
            document.getElementById('manage-forms').addEventListener('click', showFormsModal);
        });

        function addField(type) {
            fieldCount++;
            const fieldHtml = createFieldHTML(type, fieldCount);
            document.getElementById('form-builder-area').insertAdjacentHTML('beforeend', fieldHtml);
        }

        function createFieldHTML(type, index) {
            const fieldConfig = fieldTypes[type];
            let optionsHtml = '';

            if (['multiple-choice', 'checkboxes', 'dropdown'].includes(type)) {
                optionsHtml = `
                    <div class="options-container">
                        <label>Options:</label>
                        <div class="option-items">
                            <div class="option-item">
                                <input type="text" placeholder="Option 1" class="option-input">
                                <button type="button" onclick="removeOption(this)" class="text-red-500 ml-2">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="add-option" onclick="addOption(this)">Add Option</button>
                    </div>
                `;
            }

            return `
                <div class="field-card" data-type="${type}">
                    <div class="field-header">
                        <span>
                            <i class="fas ${fieldConfig.icon} mr-2"></i>
                            ${fieldConfig.label}
                        </span>
                        <div class="actions">
                            <button type="button" onclick="moveFieldUp(this)">
                                <i class="fas fa-arrow-up"></i>
                            </button>
                            <button type="button" onclick="moveFieldDown(this)">
                                <i class="fas fa-arrow-down"></i>
                            </button>
                            <button type="button" onclick="removeField(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="field-body">
                        <div class="form-group">
                            <label>Question</label>
                            <input type="text" class="form-control field-question" placeholder="Enter question" required>
                        </div>
                        <div class="form-group">
                            <label>Placeholder</label>
                            <input type="text" class="form-control field-placeholder" placeholder="Enter placeholder">
                        </div>
                        <div class="required-checkbox">
                            <input type="checkbox" class="field-required">
                            <label>Required field</label>
                        </div>
                        ${optionsHtml}
                    </div>
                </div>
            `;
        }

        function addOption(button) {
            const optionsContainer = button.previousElementSibling;
            const optionCount = optionsContainer.children.length + 1;
            const optionHtml = `
                <div class="option-item">
                    <input type="text" placeholder="Option ${optionCount}" class="option-input">
                    <button type="button" onclick="removeOption(this)" class="text-red-500 ml-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        }

        function removeOption(button) {
            button.parentElement.remove();
        }

        function removeField(button) {
            button.closest('.field-card').remove();
        }

        function moveFieldUp(button) {
            const field = button.closest('.field-card');
            const prevField = field.previousElementSibling;
            if (prevField) {
                field.parentNode.insertBefore(field, prevField);
            }
        }

        function moveFieldDown(button) {
            const field = button.closest('.field-card');
            const nextField = field.nextElementSibling;
            if (nextField) {
                field.parentNode.insertBefore(nextField, field);
            }
        }

        async function saveForm() {
            const formData = collectFormData();
            
            try {
                const response = await fetch('/api/forms/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Form saved successfully!');
                    currentFormId = result.formId;
                    
                    // Update Google Sheet with existing data
                    await updateGoogleSheet(result.formId);
                } else {
                    alert('Error saving form: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving form');
            }
        }

        function collectFormData() {
            const fields = [];
            const fieldCards = document.querySelectorAll('.field-card');

            fieldCards.forEach((card, index) => {
                const fieldData = {
                    type: card.dataset.type,
                    question: card.querySelector('.field-question').value,
                    placeholder: card.querySelector('.field-placeholder').value,
                    required: card.querySelector('.field-required').checked,
                    order: index
                };

                // Collect options for choice-based fields
                if (['multiple-choice', 'checkboxes', 'dropdown'].includes(fieldData.type)) {
                    const options = [];
                    card.querySelectorAll('.option-input').forEach(input => {
                        if (input.value.trim()) {
                            options.push(input.value.trim());
                        }
                    });
                    fieldData.options = options;
                }

                fields.push(fieldData);
            });

            return {
                formId: currentFormId,
                title: document.getElementById('form-title').value,
                description: document.getElementById('form-description').value,
                status: document.getElementById('form-status').value,
                fields: fields
            };
        }

        

        async function updateGoogleSheet(formId) {
            try {
                const response = await fetch(googleSheetEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    console.log('Google Sheet updated successfully');
                } else {
                    console.error('Error updating Google Sheet:', result.message);
                }
            } catch (error) {
                console.error('Error syncing with Google Sheet:', error);
            }
        }

        function previewForm() {
            const formData = collectFormData();
            // Store form data temporarily for preview
            sessionStorage.setItem('previewForm', JSON.stringify(formData));
            window.open('/forms/preview', '_self');
        }

        function showFormsModal() {
            loadFormsList();
            document.getElementById('forms-modal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('forms-modal').style.display = 'none';
        }

        async function loadFormsList() {
            try {
                const response = await fetch('/api/forms');
                const forms = await response.json();
                
                const formsList = document.getElementById('forms-list');
                formsList.innerHTML = forms.map(form => `
                    <div class="form-item p-3 border-b">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="font-bold">${form.title}</h3>
                                <p class="text-sm text-gray-600">${form.description}</p>
                                <span class="status-badge ${form.is_active ? 'status-active' : 'status-inactive'}">
                                    ${form.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div>
                                <button onclick="editForm(${form.id})" class="btn btn-primary btn-sm">Edit</button>
                                <button onclick="deleteForm(${form.id})" class="btn btn-danger btn-sm">Delete</button>
                                <button onclick="viewResponses(${form.id})" class="btn btn-secondary btn-sm">View Data</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading forms:', error);
            }
        }

        async function editForm(formId) {
            try {
                const response = await fetch(`/api/forms/${formId}`);
                const form = await response.json();
                
                // Populate form data
                document.getElementById('form-title').value = form.title;
                document.getElementById('form-description').value = form.description;
                document.getElementById('form-status').value = form.is_active ? 'active' : 'inactive';
                
                // Clear existing fields
                document.getElementById('form-builder-area').innerHTML = '';
                
                // Add fields
                form.fields.forEach(field => {
                    addField(field.type);
                    const lastField = document.querySelector('.field-card:last-child');
                    lastField.querySelector('.field-question').value = field.question;
                    lastField.querySelector('.field-placeholder').value = field.placeholder || '';
                    lastField.querySelector('.field-required').checked = field.is_required;
                    
                    // Add options if any
                    if (field.options && field.options.length > 0) {
                        field.options.forEach((option, index) => {
                            if (index === 0) {
                                lastField.querySelector('.option-input').value = option;
                            } else {
                                addOption(lastField.querySelector('.add-option'));
                                lastField.querySelectorAll('.option-input')[index].value = option;
                            }
                        });
                    }
                });
                
                currentFormId = formId;
                closeModal();
            } catch (error) {
                console.error('Error loading form:', error);
            }
        }

        async function deleteForm(formId) {
            if (confirm('Are you sure you want to delete this form?')) {
                try {
                    const response = await fetch(`/api/forms/${formId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (response.ok) {
                        loadFormsList();
                    }
                } catch (error) {
                    console.error('Error deleting form:', error);
                }
            }
        }

        function viewResponses(formId) {
            window.open(`/forms/${formId}/responses`, '_blank');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('forms-modal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>