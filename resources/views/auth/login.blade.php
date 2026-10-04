@extends('layouts.app')

@section('title', 'Sign In - CargoTrace')

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-sky-600 text-white font-black text-2xl shadow-lg mb-4">
            CT
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">CargoTrace Platform</h2>
        <p class="mt-2 text-sm text-slate-600">
            Sign in to access your shipment visibility dashboard
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl sm:px-10 border border-slate-200">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input type="email" name="email" id="email" required autofocus value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <label for="remember" class="ml-2 block text-xs text-slate-600">Remember me</label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition">
                        Sign In
                    </button>
                </div>
            </form>

            <!-- Quick Demo Accounts (UAS Evaluation Helper) -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                    Quick Demo Credentials (One-Click Fill)
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillCreds('admin@cargotrace.test', 'password')"
                        class="text-xs py-1.5 px-2 rounded bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-medium transition text-center">
                        Admin
                    </button>
                    <button type="button" onclick="fillCreds('operator@cargotrace.test', 'password')"
                        class="text-xs py-1.5 px-2 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-medium transition text-center">
                        Operator
                    </button>
                    <button type="button" onclick="fillCreds('customer@cargotrace.test', 'password')"
                        class="text-xs py-1.5 px-2 rounded bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-medium transition text-center">
                        Customer
                    </button>
                </div>
            </div>

            <div class="mt-4 text-center text-xs text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-sky-600 hover:text-sky-500 underline">Register as Customer</a>
            </div>
        </div>
    </div>
</div>

<script>
    function fillCreds(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
