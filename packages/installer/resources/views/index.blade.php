<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="https://tecdiary.com/img/icon.svg" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Tecdiary Application Installer</title>
  <script>
    document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches);
  </script>
  <style>
    html {
      background-color: oklch(1 0 0);
    }

    html.dark {
      background-color: oklch(0.145 0 0);
    }

    [x-cloak] {
      display: none !important;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script>
    function installer() {
      return {
        step: 1,
        mail: false,
        error: null,
        saving: false,
        demoSQL: true,
        message: null,
        loading: false,
        imported: false,
        importing: false,
        form_errors: null,
        server_errors: null,
        steps: [{
            step: 1,
            title: 'Get Started',
            description: 'Prepare the Details'
          },
          {
            step: 2,
            title: 'Server',
            description: 'Server Requirements'
          },
          {
            step: 3,
            title: 'Settings',
            description: 'Fill the Settings'
          },
          {
            step: 4,
            title: 'Finalize',
            description: 'Finalize the Installation'
          },
        ],
        form: {
          license: {
            username: '',
            code: ''
          },
          account: {
            name: '',
            username: '',
            email: '',
            password: '',
            password_confirmation: ''
          },
          database: {
            host: '127.0.0.1',
            port: 3306,
            name: '',
            user: '',
            password: '',
            socket: ''
          },
          mail: {
            driver: 'log',
            host: '',
            port: '',
            username: '',
            password: '',
            path: ''
          },
        },

        getError(key) {
          if (this.form_errors && this.form_errors[key]) {
            return this.form_errors[key].join(', ');
          }
          return null;
        },

        fieldError(key, prefix) {
          const err = this.getError(key);
          if (!err) return null;
          return err.replaceAll(prefix, '');
        },

        headers() {
          return {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          };
        },

        goTo(target) {
          if (target <= this.step) {
            this.step = target;
            window.scrollTo(0, 0);
          }
        },

        prevStep() {
          if (this.step > 1) {
            this.step--;
            window.scrollTo(0, 0);
          }
        },

        async checkServer() {
          this.loading = true;
          try {
            const res = await fetch('/install/check', {
              method: 'POST',
              headers: this.headers(),
            });
            const data = await res.json();
            setTimeout(() => (this.loading = false), 100);
            if (res.ok) {
              if (data.success) {
                this.error = null;
                this.server_errors = null;
                this.step = 3;
              } else {
                this.error = data.message;
                this.server_errors = data.errors;
                this.step = 2;
              }
            } else {
              this.server_errors = data.errors;
              console.log(res, data);
            }
          } catch (e) {
            this.loading = false;
            console.log(e);
          }
        },

        async updateSettings() {
          this.loading = true;
          try {
            const res = await fetch('/install/save', {
              method: 'POST',
              headers: this.headers(),
              body: JSON.stringify(this.form),
            });
            const data = await res.json();
            setTimeout(() => (this.loading = false), 100);
            if (res.ok) {
              if (data.success) {
                this.error = null;
                this.form_errors = null;
                this.step = 4;
              } else {
                this.error = data.message;
                this.form_errors = data.errors;
              }
            } else {
              this.error = data.message;
              this.form_errors = data.errors;
              window.scrollTo(0, 0);
              console.log(res, data);
            }
          } catch (e) {
            this.loading = false;
            console.log(e);
          }
        },

        async demoData(minimal = false) {
          this.loading = true;
          if (!minimal) this.importing = true;
          try {
            const res = await fetch('/install/demo', {
              method: 'POST',
              body: JSON.stringify({
                done: 'yes',
                minimal
              }),
              headers: this.headers(),
            });
            const data = await res.json();
            setTimeout(() => (this.loading = false), 100);
            if (res.ok) {
              if (data.success) {
                this.error = null;
                this.imported = true;
                this.form_errors = null;
                this.message = data.message;
                this.step = 4;
              } else {
                this.error = data.message;
                this.form_errors = data.errors;
              }
            } else {
              this.error = data.message;
              this.form_errors = data.errors;
              console.log(res, data);
            }
          } catch (e) {
            this.loading = false;
            console.log(e);
          } finally {
            this.importing = false;
          }
        },

        async finalize() {
          this.saving = true;
          try {
            const res = await fetch('/install/finalize', {
              method: 'POST',
              body: JSON.stringify({
                done: 'yes'
              }),
              headers: this.headers(),
            });
            const data = await res.json();
            setTimeout(() => (this.saving = false), 1500);
            if (res.ok) {
              if (data.success) {
                this.error = null;
                this.form_errors = null;
                window.location.href = window.location.origin;
                setTimeout(() => window.location.reload(), 300);
              } else {
                this.error = data.message;
                this.form_errors = data.errors;
              }
            } else {
              this.error = data.message;
              this.form_errors = data.errors;
              console.log(res, data);
            }
          } catch (e) {
            this.saving = false;
            console.log(e);
          }
        },
      };
    }
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="min-h-screen bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-50" x-data="installer()" x-cloak>
  <div class="flex min-h-screen w-full flex-col items-center justify-center p-6 sm:p-10">
    <div class="mb-6 flex flex-col items-center justify-center">
      <img src="https://tecdiary.com/img/logo.svg" alt="Tecdiary" class="mb-1 h-10 w-auto dark:hidden" />
      <img src="https://tecdiary.com/img/logo-light.svg" alt="Tecdiary" class="mb-1 hidden h-10 w-auto dark:block" />
      <h1 class="text-center text-3xl font-thin">Application Installation</h1>
    </div>

    <div class="w-full max-w-3xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
      {{-- Stepper --}}
      <div class="flex w-full gap-2">
        <template x-for="(s, idx) in steps" :key="s.step">
          <div class="relative flex w-full flex-col items-center justify-center">
            <div x-show="idx < steps.length - 1"
              class="absolute top-5 right-[calc(-50%+10px)] left-[calc(50%+20px)] block h-0.5 shrink-0 rounded-full"
              :class="step > s.step ? 'bg-zinc-900 dark:bg-zinc-50' : 'bg-zinc-200 dark:bg-zinc-800'"></div>

            <button type="button" x-on:click="goTo(s.step)" x-bind:disabled="s.step > step"
              class="z-10 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-medium transition-colors disabled:pointer-events-none disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-950 focus-visible:ring-offset-2 dark:focus-visible:ring-zinc-300"
              :class="(step >= s.step) ?
              'bg-zinc-900 text-zinc-50 hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90' :
              'border border-zinc-200 bg-white text-zinc-900 hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:hover:bg-zinc-800'"
              :class="step === s.step ? 'ring-2 ring-zinc-950 ring-offset-2 ring-offset-white dark:ring-zinc-300 dark:ring-offset-zinc-950' : ''">
              {{-- Check (completed) --}}
              <svg x-show="step > s.step" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5" />
              </svg>
              {{-- Circle (active) --}}
              <svg x-show="step === s.step" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
              </svg>
              {{-- Dot (inactive) --}}
              <svg x-show="step < s.step" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12.1" cy="12.1" r="1" />
              </svg>
            </button>

            <div class="mt-5 flex flex-col items-center text-center">
              <span class="text-sm font-semibold transition lg:text-base"
                :class="step === s.step ? 'text-zinc-900 dark:text-zinc-50' : 'text-zinc-500 dark:text-zinc-400'" x-text="s.title"></span>
              <span class="hidden text-xs text-zinc-500 transition md:block lg:text-sm dark:text-zinc-400"
                :class="step === s.step ? 'text-zinc-900 dark:text-zinc-50' : ''" x-text="s.description"></span>
            </div>
          </div>
        </template>
      </div>

      {{-- Step Content --}}
      <div class="mt-12 flex flex-col gap-4">
        {{-- Step 1 --}}
        <template x-if="step === 1">
          <div class="space-y-6">
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-800">
              <h1 class="text-lg leading-6 font-medium">Let's Install</h1>
              <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Let's get started by preparing the information that we will need to install the application.
              </p>
            </div>

            <div class="relative w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm dark:border-zinc-800">
              <h5 class="mb-1 text-base font-medium leading-none tracking-tight">Free support for 6 months!</h5>
              <div class="text-sm text-zinc-500 dark:text-zinc-400">
                <p>Each purchase comes with free support for 6 months.</p>
                <p class="mt-1.5 font-bold">
                  We don't offer email support, so please don't email to ask support questions instead ask questions at
                  <a target="_blank" class="font-semibold text-zinc-900 underline dark:text-zinc-50"
                    href="https://tecdiary.com/support/questions/stock-manager-advance-with-all-modules">Item Support Page</a>.
                </p>
              </div>
            </div>

            <div class="mt-6 flow-root">
              <ol class="-my-5 divide-y divide-zinc-200 dark:divide-zinc-800">
                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">1. Server Requirements</h3>
                    <p class="mt-1 pl-4 text-sm">
                      <strong>PHP 8.4+</strong>, <strong>MySQL 8+</strong> or <strong>MariaDB 10.3+</strong>
                    </p>
                  </div>
                </li>
                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">2. Purchase Verification</h3>
                    <p class="mt-1 pl-4 text-sm">
                      We will need your <strong>account username</strong> and <strong>license key/purchase code</strong> to verify your
                      purchase.
                    </p>
                  </div>
                </li>
                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">3. Database Server Details</h3>
                    <p class="mt-1 pl-4 text-sm">
                      We will need your database <strong>host</strong>, <strong>port</strong>, <strong>user</strong> and
                      <strong>password</strong> to connect to your database.
                    </p>
                  </div>
                </li>
                <li x-show="mail" class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">4. Mail Server Details</h3>
                    <div class="mt-1 pl-4 text-sm">
                      We will need your mail server details, the driver options are
                      <ol class="pl-6">
                        <li>
                          SMTP: <strong>host</strong>, <strong>port</strong>, <strong>user</strong>, <strong>password</strong> and
                          <strong>encryption</strong>
                        </li>
                        <li>SendMail: <strong>path</strong></li>
                      </ol>
                      <p class="mt-2">This is required so that system can send emails.</p>
                    </div>
                  </div>
                </li>
              </ol>
            </div>
          </div>
        </template>

        {{-- Step 2 --}}
        <template x-if="step === 2">
          <div class="space-y-6">
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-800">
              <h1 class="text-lg leading-6 font-medium">Server</h1>
              <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Let's check the server software versions and extensions.</p>
            </div>

            <div class="mt-6 flow-root">
              <template x-if="server_errors">
                <div
                  class="relative w-full rounded-lg border border-red-500/50 px-4 py-3 pl-11 text-sm text-red-600 dark:border-red-500 dark:text-red-500">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute top-4 left-4 h-4 w-4">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                  </svg>
                  <h5 class="mb-1 font-medium leading-none tracking-tight" x-text="error"></h5>
                  <div class="text-sm">
                    <ul class="list-disc space-y-1 pl-5">
                      <template x-for="(err, index) in server_errors" :key="index">
                        <li x-text="err"></li>
                      </template>
                    </ul>
                  </div>
                </div>
              </template>
              <template x-if="!server_errors">
                <div class="relative w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm dark:border-zinc-800">
                  <h5 class="mb-1 font-medium leading-none tracking-tight">Success!</h5>
                  <div class="text-sm text-zinc-500 dark:text-zinc-400">
                    Your server php version and extensions are fine. Please proceed to settings.
                  </div>
                </div>
              </template>
            </div>
          </div>
        </template>

        {{-- Step 3 --}}
        <template x-if="step === 3">
          <div class="space-y-6">
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-800">
              <h1 class="text-lg leading-6 font-medium">Settings</h1>
              <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Please fill the information to proceed to next step.</p>
            </div>

            <div x-show="error"
              class="relative w-full rounded-lg border border-red-500/50 px-4 py-3 text-sm text-red-600 dark:border-red-800 dark:text-red-200 bg-red-100 dark:bg-red-900">
              <h5 class="font-medium leading-none tracking-tight" x-text="error"></h5>
            </div>

            <div class="mt-6 flow-root">
              <ol class="-my-5 divide-y divide-zinc-200 dark:divide-zinc-800">
                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">1. Purchase Verification</h3>
                    <div class="space-y-6 pt-2 pl-4 sm:space-y-5">
                      <div class="sm:grid sm:grid-cols-2 sm:items-start sm:gap-6">
                        <div>
                          <label for="account-username" class="block pb-1 text-sm font-medium leading-none">Username</label>
                          <div class="mt-1">
                            <input type="text" autofocus id="account-username" name="account_username"
                              x-model="form.license.username"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('license.username') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('license.username')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('license.username', 'license.')"></div>
                        </div>
                        <div>
                          <label for="purchase-code" class="block pb-1 text-sm font-medium leading-none">License Key</label>
                          <div class="mt-1">
                            <input type="text" id="purchase-code" name="purchase_code" x-model="form.license.code"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('license.code') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('license.code')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('license.code', 'license.')"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>

                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">2. Database Server Details</h3>
                    <div class="space-y-6 pt-2 pl-4 sm:space-y-5">
                      <div class="sm:grid sm:grid-cols-2 sm:items-start sm:gap-6">
                        <div>
                          <label for="database-host" class="block pb-1 text-sm font-medium leading-none">Host</label>
                          <div class="mt-1">
                            <input type="text" id="database-host" name="database_host" x-model="form.database.host"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.host') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.host')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.host', 'database.')"></div>
                        </div>
                        <div>
                          <label for="database-port" class="block pb-1 text-sm font-medium leading-none">Port</label>
                          <div class="mt-1">
                            <input type="text" id="database-port" name="database_port" x-model="form.database.port"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.port') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.port')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.port', 'database.')"></div>
                        </div>
                        <div>
                          <label for="database-username" class="block pb-1 text-sm font-medium leading-none">Username</label>
                          <div class="mt-1">
                            <input type="text" id="database-username" name="database_username" x-model="form.database.user"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.user') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.user')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.user', 'database.')"></div>
                        </div>
                        <div>
                          <label for="database-password" class="block pb-1 text-sm font-medium leading-none">Password</label>
                          <div class="mt-1">
                            <input type="password" id="database-password" name="database_password" x-model="form.database.password"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.password') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.password')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.password', 'database.')"></div>
                        </div>
                        <div>
                          <label for="database-name" class="block pb-1 text-sm font-medium leading-none">Database Name</label>
                          <div class="mt-1">
                            <input type="text" id="database-name" name="database_name" x-model="form.database.name"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.name') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.name')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.name', 'database.')"></div>
                        </div>
                        <div>
                          <label for="database-socket" class="block pb-1 text-sm font-medium leading-none">Socket</label>
                          <div class="mt-1">
                            <input type="text" id="database-socket" name="database_socket" x-model="form.database.socket"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('database.socket') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('database.socket')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('database.socket', 'database.')"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="relative mt-6 ml-4 w-full rounded-lg border border-zinc-200 px-4 py-3 pl-11 text-sm dark:border-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="absolute top-4 left-4 h-4 w-4">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="8" x2="12" y2="12" />
                      <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                      You can edit these settings in your <code>.env</code> file later.
                    </div>
                  </div>
                </li>

                <li x-show="mail" class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">3. Mail Server Details</h3>
                    <div class="space-y-6 pt-2 pl-4 sm:space-y-5">
                      <div class="sm:grid sm:grid-cols-2 sm:items-start sm:gap-6">
                        <div>
                          <label for="mail-driver" class="block pb-1 text-sm font-medium leading-none">Mail Driver</label>
                          <div class="mt-1">
                            <select id="mail-driver" name="mail_driver" x-model="form.mail.driver"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-800 dark:focus-visible:ring-zinc-300">
                              <option value="smtp">SMTP</option>
                              <option value="sendmail">SendMail</option>
                            </select>
                          </div>
                          <div x-show="getError('mail.driver')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('mail.driver', 'mail.')"></div>
                        </div>

                        <template x-if="form.mail.driver == 'smtp'">
                          <div class="contents">
                            <div>
                              <label for="mail-host" class="block pb-1 text-sm font-medium leading-none">Host</label>
                              <div class="mt-1">
                                <input type="text" id="mail-host" name="mail_host" x-model="form.mail.host"
                                  class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                                  :class="getError('mail.host') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                              </div>
                              <div x-show="getError('mail.host')" class="mt-1 text-sm text-red-500"
                                x-text="fieldError('mail.host', 'mail.')"></div>
                            </div>
                            <div>
                              <label for="mail-port" class="block pb-1 text-sm font-medium leading-none">Port</label>
                              <div class="mt-1">
                                <input type="text" id="mail-port" name="mail_port" x-model="form.mail.port"
                                  class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                                  :class="getError('mail.port') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                              </div>
                              <div x-show="getError('mail.port')" class="mt-1 text-sm text-red-500"
                                x-text="fieldError('mail.port', 'mail.')"></div>
                            </div>
                            <div>
                              <label for="mail-username" class="block pb-1 text-sm font-medium leading-none">Username</label>
                              <div class="mt-1">
                                <input type="text" id="mail-username" name="mail_username" x-model="form.mail.username"
                                  class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                                  :class="getError('mail.username') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                              </div>
                              <div x-show="getError('mail.username')" class="mt-1 text-sm text-red-500"
                                x-text="fieldError('mail.username', 'mail.')"></div>
                            </div>
                            <div>
                              <label for="mail-password" class="block pb-1 text-sm font-medium leading-none">Password</label>
                              <div class="mt-1">
                                <input type="password" id="mail-password" name="mail_password" x-model="form.mail.password"
                                  class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                                  :class="getError('mail.password') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                              </div>
                              <div x-show="getError('mail.password')" class="mt-1 text-sm text-red-500"
                                x-text="fieldError('mail.password', 'mail.')"></div>
                            </div>
                          </div>
                        </template>

                        <template x-if="form.mail.driver == 'sendmail'">
                          <div>
                            <label for="mail-path" class="block pb-1 text-sm font-medium leading-none">Path</label>
                            <div class="mt-1">
                              <input type="text" id="mail-path" name="mail_path" x-model="form.mail.path"
                                class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                                :class="getError('mail.path') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                            </div>
                            <div x-show="getError('mail.path')" class="mt-1 text-sm text-red-500"
                              x-text="fieldError('mail.path', 'mail.')"></div>
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                  <div class="relative mt-6 ml-4 w-full rounded-lg border border-zinc-200 px-4 py-3 pl-11 text-sm dark:border-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="absolute top-4 left-4 h-4 w-4">
                      <circle cx="12" cy="12" r="10" />
                      <line x1="12" y1="8" x2="12" y2="12" />
                      <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                      You can edit these settings in your <code>.env</code> file later.
                    </div>
                  </div>
                </li>

                <li class="py-8">
                  <div class="relative">
                    <h3 class="mb-3 text-sm font-semibold">
                      <span x-text="mail ? '4' : '3'"></span>. Create Admin Account
                    </h3>
                    <div class="space-y-6 pt-2 pl-4 sm:space-y-5">
                      <div class="sm:grid sm:grid-cols-2 sm:items-start sm:gap-6">
                        <div class="col-span-full">
                          <label for="username-name" class="block pb-1 text-sm font-medium leading-none">Full Name</label>
                          <div class="mt-1">
                            <input type="text" id="username-name" name="username_name" x-model="form.account.name"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('account.name') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('account.name')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('account.name', 'account.')"></div>
                        </div>
                        <div>
                          <label for="username-username" class="block pb-1 text-sm font-medium leading-none">Username</label>
                          <div class="mt-1">
                            <input type="text" id="username-username" name="username" x-model="form.account.username"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('account.username') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('account.username')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('account.username', 'account.')"></div>
                        </div>
                        <div>
                          <label for="username-email" class="block pb-1 text-sm font-medium leading-none">Email Address</label>
                          <div class="mt-1">
                            <input type="email" id="username-email" name="username_email" x-model="form.account.email"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('account.email') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('account.email')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('account.email', 'account.')"></div>
                        </div>
                        <div>
                          <label for="username-password" class="block pb-1 text-sm font-medium leading-none">Password</label>
                          <div class="mt-1">
                            <input type="password" id="username-password" name="username_password" x-model="form.account.password"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('account.password') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('account.password')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('account.password', 'account.')"></div>
                        </div>
                        <div>
                          <label for="username-password-confirmation" class="block pb-1 text-sm font-medium leading-none">Confirm
                            Password</label>
                          <div class="mt-1">
                            <input type="password" id="username-password-confirmation" name="database_password_confirmation"
                              x-model="form.account.password_confirmation"
                              class="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm shadow-xs transition-colors placeholder:text-zinc-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:cursor-not-allowed disabled:opacity-50 dark:placeholder:text-zinc-400 dark:focus-visible:ring-zinc-300"
                              :class="getError('account.password_confirmation') ? 'border-red-500' : 'border-zinc-200 dark:border-zinc-800'" />
                          </div>
                          <div x-show="getError('account.password_confirmation')" class="mt-1 text-sm text-red-500"
                            x-text="fieldError('account.password_confirmation', 'account.')"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              </ol>
            </div>
          </div>
        </template>

        {{-- Step 4 --}}
        <template x-if="step === 4">
          <div class="space-y-6">
            <div>
              <h1 class="text-lg leading-6 font-medium">We're almost done</h1>
              <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Please finalize the installation.</p>
            </div>

            <div class="mt-8 relative w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm dark:border-zinc-800">
              <h5 class="mb-1 font-medium leading-none tracking-tight">Settings Updated!</h5>
              <div class="text-sm text-zinc-500 dark:text-zinc-400">
                <p>The settings has been updated. Please finalize the installation.</p>
              </div>
            </div>

            <div x-show="demoSQL" class="mt-8 relative w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm dark:border-zinc-800">
              <h5 class="mb-1 font-medium leading-none tracking-tight">Default Data!</h5>
              <div class="text-sm text-zinc-500 dark:text-zinc-400">
                You can import the default data (account, store, category and product) by clicking the button below.

                <div class="mt-4">
                  <button type="button" x-on:click="demoData(true)" x-bind:disabled="saving || loading || imported"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 bg-zinc-100 text-zinc-900 hover:bg-zinc-100/80 dark:bg-zinc-800 dark:text-zinc-50 dark:hover:bg-zinc-800/80 h-9 px-4 py-2">
                    Add Default Data (minimal)
                    <svg x-show="loading && !importing" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round" class="h-4 w-4 animate-spin">
                      <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <div x-show="demoSQL" class="mt-8 relative w-full rounded-lg border border-zinc-200 px-4 py-3 text-sm dark:border-zinc-800">
              <h5 class="mb-1 font-medium leading-none tracking-tight">Demo Data!</h5>
              <div class="text-sm text-zinc-500 dark:text-zinc-400">
                If you are installing for demo/testing purpose, you can import the demo data by clicking the button below.

                <div class="mt-4">
                  <button type="button" x-on:click="demoData(false)" disabled
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-zinc-200 bg-white text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:hover:bg-zinc-800 h-9 px-4 py-2">
                    Import
                    <svg x-show="importing" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="h-4 w-4 animate-spin">
                      <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

      {{-- Footer --}}
      <div class="mt-10 flex items-center justify-between">
        <button type="button" x-on:click="prevStep()" x-bind:disabled="step <= 1"
          class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md border border-zinc-200 bg-white text-sm font-medium text-zinc-900 transition-colors hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50 dark:hover:bg-zinc-800 h-9 px-4 py-2">
          Back
        </button>
        <div class="flex items-center gap-3">
          <button type="button" x-show="step === 1" x-on:click="checkServer()" x-bind:disabled="loading"
            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 bg-zinc-900 text-zinc-50 shadow-sm hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90 h-9 px-4 py-2">
            Next
            <svg x-show="loading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 animate-spin">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
          </button>
          <button type="button" x-show="step === 2" x-on:click="checkServer()" x-bind:disabled="loading"
            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 bg-zinc-900 text-zinc-50 shadow-sm hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90 h-9 px-4 py-2">
            <svg x-show="loading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 animate-spin">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
            <span x-text="error ? 'Re-Check' : 'Next'"></span>
          </button>
          <button type="button" x-show="step === 3" x-on:click="updateSettings()" x-bind:disabled="loading"
            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 bg-zinc-900 text-zinc-50 shadow-sm hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90 h-9 px-4 py-2">
            <svg x-show="loading" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 animate-spin">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
            Save &amp; Next
          </button>
          <button type="button" x-show="step === 4" x-on:click="finalize()" x-bind:disabled="saving || loading"
            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-zinc-950 disabled:pointer-events-none disabled:opacity-50 bg-zinc-900 text-zinc-50 shadow-sm hover:bg-zinc-900/90 dark:bg-zinc-50 dark:text-zinc-900 dark:hover:bg-zinc-50/90 h-9 px-4 py-2">
            <svg x-show="saving" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 animate-spin">
              <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
            Finalize
          </button>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
