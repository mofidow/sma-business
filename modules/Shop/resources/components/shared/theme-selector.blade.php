<div class="h-full flex items-center justify-center">
  <div x-data="{
      open: false,
      theme: localStorage.getItem('theme') || 'system',
      selectTheme(theme) {
          this.open = false;
          this.theme = theme;
          this.updateTheme();
      },
      init() {
          this.updateTheme();
      },
      updateTheme() {
          if (this.theme === 'dark') {
              document.documentElement.classList.add('dark');
              document.documentElement.style.colorScheme = 'dark';
          } else if (this.theme === 'light') {
              document.documentElement.classList.remove('dark');
              document.documentElement.style.colorScheme = 'light';
          } else {
              if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                  document.documentElement.classList.add('dark');
                  document.documentElement.style.colorScheme = 'dark';
              } else {
                  document.documentElement.classList.remove('dark');
                  document.documentElement.style.colorScheme = 'light';
              }
          }
          this.$watch('theme', value => {
              localStorage.setItem('theme', value);
              if (localStorage.theme === 'dark') {
                  document.documentElement.classList.add('dark');
                  document.documentElement.style.colorScheme = 'dark';
              } else if (localStorage.theme === 'light') {
                  document.documentElement.classList.remove('dark');
                  document.documentElement.style.colorScheme = 'light';
              }
          });
      }
  }" @click.away="open = false" class="relative z-30 text-start text-sm">
    <button type="button" @click="open = true"
      class="flex items-center px-2.5 link hover:bg-gray-100 dark:hover:bg-gray-700 -my-2.5 py-2.5 rounded-md">
      <svg x-show="theme == 'dark'" class="size-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
      </svg>
      <svg x-show="theme == 'light'" class="size-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path
          d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
          fill-rule="evenodd" clip-rule="evenodd"></path>
      </svg>
      <svg x-show="theme == 'system'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
        <path fill-rule="evenodd"
          d="M2.25 5.25a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3V15a3 3 0 0 1-3 3h-3v.257c0 .597.237 1.17.659 1.591l.621.622a.75.75 0 0 1-.53 1.28h-9a.75.75 0 0 1-.53-1.28l.621-.622a2.25 2.25 0 0 0 .659-1.59V18h-3a3 3 0 0 1-3-3V5.25Zm1.5 0v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5Z"
          clip-rule="evenodd" />
      </svg>
    </button>


    <div x-show="open" style="display: none" x-transition:enter="transition ease-out duration-100"
      x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
      x-transition:leave-end="transform opacity-0 scale-95"
      class="absolute end-0 z-10 mt-4 w-28 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 focus:outline-none"
      role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
      <div class="py-1" role="none">
        <a @click="selectTheme('light')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus"
          role="menuitem" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
          </svg>
          {{ __('Light') }}
        </a>
        <a @click="selectTheme('dark')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus"
          role="menuitem" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
          </svg>
          {{ __('Dark') }}
        </a>
        <a @click="selectTheme('system')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus"
          role="menuitem" tabindex="-1">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-5">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
          </svg>
          {{ __('System') }}
        </a>
      </div>
    </div>
  </div>
</div>
