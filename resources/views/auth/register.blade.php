@extends('layouts.app')

@section('title', 'Register - CargoTrace')

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Create Customer Account</h2>
        <p class="mt-2 text-sm text-slate-600">
            Sign up to track and monitor your global shipments
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl rounded-2xl sm:px-10 border border-slate-200">
            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="company_name" class="block text-sm font-medium text-slate-700">Company Name</label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}"
                        placeholder="e.g. Acme Logistics Global"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        placeholder="+62 812 3456 7890"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500 text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition">
                        Register Account
                    </button>
                </div>
            </form>

            <div class="mt-4 text-center text-xs text-slate-500">
                Already registered?
                <a href="{{ route('login') }}" class="font-medium text-sky-600 hover:text-sky-500 underline">Sign In</a>
            </div>
        </div>
    </div>
</div>
@endsection
