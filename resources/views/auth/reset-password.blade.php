<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - Calaia PH</title>
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            padding: 2.5rem;
            margin: 2rem;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary:disabled {
            background: #93c5fd;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 640px) {
            .auth-card {
                padding: 1.5rem;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Reset Password</h1>
                <p>Enter your new password below to reset your account password.</p>
            </div>
            
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif
            
            <form id="resetPasswordForm" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                
                <div class="form-group">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" id="password" name="password" 
                           class="form-input" 
                           placeholder="Enter your new password"
                           minlength="8"
                           required autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="form-input" 
                           placeholder="Confirm your new password"
                           minlength="8"
                           required autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn-primary">
                    Reset Password
                </button>
            </form>
            
            <div class="back-link">
                <a href="{{ route('login') }}">Back to login</a>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Resetting...';
                submitBtn.disabled = true;
                
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                
                fetch('{{ route('reset.password.post') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                })
                .then(async res => {
                    let responseData;
                    try {
                        responseData = await res.json();
                    } catch {
                        throw new Error('Invalid JSON response');
                    }
                    
                    if (res.ok) {
                        if (responseData.success) {
                            alert(responseData.message);
                            window.location.href = '{{ route('login') }}';
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    } else {
                        const errorMessage = responseData.message || responseData.errors ? 
                            Object.values(responseData.errors).flat().join('\n') : 
                            'Password reset failed';
                        alert(errorMessage);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('An error occurred. Please check your internet connection and try again.');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
