<x-header />

<div class="flex items-center justify-center min-h-screen bg-gray-50">
  <div class="bg-white shadow-md rounded-lg p-6 w-96">
    <h2 class="text-xl font-semibold mb-4 text-gray-800">Set Username & Password</h2>

    <form method="POST" action="{{ url('/set-credentials/' . $token) }}">
      @csrf
      <input name="username" type="text" placeholder="Username" required class="w-full mb-3 p-2 border rounded">
      <input name="password" type="password" placeholder="Password" required class="w-full mb-3 p-2 border rounded">
      <input name="password_confirmation" type="password" placeholder="Confirm Password" required class="w-full mb-4 p-2 border rounded">
      <button type="submit" class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700 transition">
        Save
      </button>
    </form>
  </div>
</div>

<x-footer />
