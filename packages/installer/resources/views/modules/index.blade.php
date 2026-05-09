<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ 'Module Manager' }}</title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-white h-full min-h-screen w-full flex items-center justify-center">
  <div class="text-center w-md">
    <img class="mx-auto h-12 w-auto" src="{{ asset('img/sma-icon.svg') }}" alt="SMA">
    <h3 class="mt-3 text-sm font-semibold text-gray-900">Enable Module</h3>
    <p class="mt-1 text-sm text-gray-500">Please provide purchase code and select module</p>
    <div class="mt-6 grid grid-cols-1 gap-y-4">
      @if ($modules['pos']['enabled'] ?? null)
        <div class="flex items-center justify-between rounded-md bg-green-100 px-4 py-2 text-sm font-bold text-green-800">

          <div class="flex items-center gap-x-2">
            <svg class="size-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            POS Module
          </div>
          <form method="POST" action="{{ route('modules.disable') }}" class="inline">
            @csrf
            <input type="hidden" name="name" value="pos">
            <button type="submit" class="text-red-500 cursor-pointer" onclick="return confirm('Are you sure?')">
              Disable
            </button>
          </form>
        </div>
      @endif

      @if ($modules['shop']['enabled'] ?? null)
        <div class="flex
              items-center justify-between rounded-md bg-green-100 px-4 py-2 text-sm font-bold text-green-800">

          <div class="flex items-center gap-x-2">
            <svg class="size-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Shop Module
          </div>
          <form method="POST" action="{{ route('modules.disable') }}" class="inline">
            @csrf
            <input type="hidden" name="name" value="shop">
            <button type="submit" class="text-red-500 cursor-pointer" onclick="return confirm('Are you sure?')">
              Disable
            </button>
          </form>
        </div>
      @endif

      @if (session('message') || session('error') || $errors->any())
        <div class="text-sm font-bold">
          @if (session('message'))
            <div class="text-green-500">
              {{ session('message') }}
            </div>
          @endif

          @if (session('error'))
            <div class="text-red-500">
              {{ session('error') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="text-red-500">
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      @endif

      @if (!$modules['pos']['enabled'] || !$modules['shop']['enabled'])
        <form method="POST" action="{{ route('modules.enable') }}" autocomplete="off">
          @csrf
          <div>
            <div class="mt-2">
              <div
                class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 has-[input:focus-within]:outline-2 has-[input:focus-within]:-outline-offset-2 has-[input:focus-within]:outline-blue-600">
                <input type="text" name="code" id="code" value="{{ old('code') }}"
                  class="block min-w-0 grow py-1.5 pl-1 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-0 sm:text-sm/6"
                  placeholder="Purchase Code" required autofocus>
                <div class="grid shrink-0 grid-cols-1 focus-within:relative">
                  <select id="name" name="name" value="{{ old('name') }}"
                    class="col-start-1 row-start-1 w-full appearance-none rounded-md py-1.5 pl-3 pr-7 text-base text-gray-500 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-blue-600 sm:text-sm/6">
                    <option value="pos" {{ $modules['pos']['enabled'] ? 'disabled' : '' }}>POS Module</option>
                    @if ($shop_module)
                      <option value="shop" {{ $modules['shop']['enabled'] ? 'disabled' : '' }}>Shop Module</option>
                    @endif
                    {{-- <option value="shop" disabled>Shop Module</option> --}}
                  </select>
                  <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4"
                    viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd"
                      d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
              @error('code')
                <div class="text-red-500 text-sm text-left mt-1">{{ $message }}</div>
              @enderror
              @error('name')
                <div class="text-red-500 text-sm text-left mt-1">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="relative flex items-center justify-between">
            {{-- <div class="absolute inset-0"></div> --}}
            <a href="/"
              class="mt-6 inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600">
              Go to Homepage
            </a>

            <button type="submit" onclick="this.disabled=true; this.form.submit();"
              class="group/button mt-6 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 cursor-pointer">
              Enable
              <span class="hidden group-disabled/button:block">
                <svg viewBox="0 0 38 38" class="size-5 ml-2" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"
                  class="stroke-current inline">
                  <g fill="none" fill-rule="evenodd">
                    <g transform="translate(1 1)" stroke-width="2">
                      <circle stroke-opacity=".3" cx="18" cy="18" r="18" />
                      <path d="M36 18c0-9.94-8.06-18-18-18">
                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                          repeatCount="indefinite" />
                      </path>
                    </g>
                  </g>
                </svg>
              </span>
            </button>
          </div>
        </form>
      @endif
    </div>
  </div>
</body>

</html>
