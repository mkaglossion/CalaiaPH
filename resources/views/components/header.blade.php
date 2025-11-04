<header 
  x-data="{ showLogin: false, showSignup: false, signupSuccess: false }" 
  class="bg-blue-600 text-white shadow-lg sticky top-0 z-50"
>
  <div class="container mx-auto flex justify-between items-center py-4 px-6">
    <h1 class="text-2xl font-bold tracking-wide">CalaiaPH</h1>

    <nav class="space-x-4">
      <button 
        @click="showLogin = true"
        class="hover:text-gray-200 font-medium transition-all duration-150">
        Log In
      </button>
      <button 
        @click="showSignup = true"
        class="bg-white text-blue-600 px-5 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-all shadow-md">
        Sign Up
      </button>
    </nav>
  </div>

  <!-- ✅ SIGN UP MODAL -->
  <div 
    x-show="showSignup"
    x-transition
    @click.self="showSignup = false"
    x-cloak
    class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 text-gray-800 relative">
      <button 
        @click="showSignup = false"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
        ✕
      </button>

      <!-- STEP 1 FORM -->
      <template x-if="!signupSuccess">
        <form 
          id="signupForm"
          class="space-y-5"
          @submit.prevent="
            const csrf = document.querySelector('meta[name=csrf-token]').getAttribute('content');
            const formData = {
              first_name: $refs.first_name.value,
              last_name: $refs.last_name.value,
              email: $refs.email.value,
              username: $refs.username.value,
              password: $refs.password.value,
              password_confirmation: $refs.password_confirmation.value,
            };

            // Optional loading indicator
            $el.querySelector('button[type=submit]').innerText = 'Sending...';
            $el.querySelector('button[type=submit]').disabled = true;

            fetch('/register-step-one', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
              },
              credentials: 'same-origin',
              body: JSON.stringify(formData)
            })
            .then(async res => {
              // handle redirect or non-JSON responses
              let data;
              try {
                data = await res.json();
              } catch {
                throw new Error('Invalid JSON');
              }

              if (res.ok) {
                if (data.message || data.success) {
                  signupSuccess = true;
                } else {
                  alert('Something went wrong.');
                }
              } else if (data.errors) {
                // Laravel validation error response
                const errorMessages = Object.values(data.errors).flat().join('\\n');
                alert(errorMessages);
              } else {
                alert(data.message || 'Request failed.');
              }
            })
            .catch(err => {
              console.error(err);
              alert('Error sending request.');
            })
            .finally(() => {
              // restore button
              const btn = $el.querySelector('button[type=submit]');
              btn.innerText = 'Sign Up';
              btn.disabled = false;
            });
          "
        >

          <h2 class="text-2xl font-semibold text-center text-blue-600 mb-2">Create Your Account</h2>
          <p class="text-sm text-center text-gray-500 mb-4">Fill in your details below to get started.</p>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
              <input x-ref="first_name" type="text" placeholder="John"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
              <input x-ref="last_name" type="text" placeholder="Doe"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input x-ref="email" type="email" placeholder="example@email.com"
              class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              required>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input x-ref="username" type="text" placeholder="myusername"
              class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              required>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
              <input x-ref="password" type="password" placeholder="********"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
              <input x-ref="password_confirmation" type="password" placeholder="********"
                class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                required>
            </div>
          </div>

          <button type="submit"
            class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
            Sign Up
          </button>

          <p class="text-center text-sm text-gray-500 mt-3">
            Already have an account? 
            <a href="#" @click.prevent="showSignup = false; showLogin = true" class="text-blue-600 hover:underline font-medium">Log in</a>
          </p>
        </form>
      </template>

      <!-- STEP 2 SUCCESS -->
      <template x-if="signupSuccess">
        <div class="text-center py-6">
          <h2 class="text-2xl font-semibold text-blue-600 mb-3">Verify Your Email</h2>
          <p class="text-gray-600 mb-6 text-sm leading-relaxed">
            We've sent a verification link to your email. Please check your inbox and click the link to activate your account.
          </p>
          <button 
            @click="showSignup = false; signupSuccess = false;" 
            class="bg-blue-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
            Got It
          </button>
        </div>
      </template>
    </div>
  </div>

  <!-- ✅ LOGIN MODAL -->
  <div 
    x-show="showLogin"
    x-transition
    @click.self="showLogin = false"
    x-cloak
    class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 text-gray-800 relative">
      <button 
        @click="showLogin = false"
        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition">
        ✕
      </button>
      <h2 class="text-2xl font-semibold mb-4 text-center text-blue-600">Log In</h2>
      <form method="POST" action="/login" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input type="text" name="username" placeholder="Enter your username"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="password" placeholder="Enter your password"
            class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            required>
        </div>
        <button type="submit"
          class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all shadow-md">
          Log In
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-3">
        Don’t have an account? 
        <a href="#" @click.prevent="showLogin = false; showSignup = true" class="text-blue-600 hover:underline font-medium">Sign up</a>
      </p>
    </div>
  </div>
</header>
