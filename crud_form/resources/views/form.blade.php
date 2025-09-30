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
}

.btn-primary { background-color: #007bff; color: #fff; }
.btn-secondary { background-color: #6c757d; color: #fff; }
.btn-danger { background-color: #dc3545; color: #fff; }

main { padding: 30px; }
h1, h2 { color: #333; }
.form-settings, .form-fields {
    background: #fdfdfd;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #eee;
    margin-bottom: 20px;
}

.form-group { margin-bottom: 15px; }
label { display: block; margin-bottom: 5px; font-weight: 500; }
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
.field-btn:hover { background: #e9e9e9; transform: translateY(-2px); }

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

.field-card:hover { transform: translateY(-3px); }

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
}
.field-header .actions button:hover { color: #dc3545; }

.field-body .form-group { margin-bottom: 10px; }
.required-checkbox {
    display: flex;
    align-items: center;
    margin-top: 10px;
}
.required-checkbox input { margin-right: 5px; }


    </style>
</head>
<body>

    <div class="container">
        <header>
            <h1>Form Builder</h1>
            <div class="header-actions">
                <button class="btn btn-primary" id="save-form">Save Form</button>
                <button class="btn btn-secondary" id="preview-form">Preview Form</button>
                <a href="{{route('logout')}}" class="btn btn-danger">Logout</button>
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
            </div>

            <div class="form-fields">
                <h2>Form Fields</h2>
                <div class="field-options">
                    <button class="field-btn" data-type="text">
                        <i class="fas fa-text-width text-2xl mb-2"></i>Short Text
                    </button>
                    <button class="field-btn" data-type="email">
                        <i class="fas fa-at text-2xl mb-2" ></i>Email
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
                    </div>
            </div>
        </main>
    </div>

    <script src="app.js"></script>
</body>

</html>
