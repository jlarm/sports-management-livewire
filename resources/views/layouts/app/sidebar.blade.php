<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    @php
        $organization = context('organization');
        $hasOrganization = $organization !== null;
        $organizationColor = null;
        $accentForeground = '#ffffff';

        $rawColor = $organization?->primary_color;
        if (is_string($rawColor) && preg_match('/^#[A-Fa-f0-9]{6}$/', $rawColor) === 1) {
            $organizationColor = $rawColor;
            [$red, $green, $blue] = sscanf($organizationColor, '#%02x%02x%02x');
            $luminance = ((0.2126 * $red) + (0.7152 * $green) + (0.0722 * $blue)) / 255;
            $accentForeground = $luminance > 0.6 ? '#111827' : '#ffffff';
        }
    @endphp
    <body
        x-data
        x-on:organization-theme-updated.window="
            const color = $event.detail.color;
            if (! color) {
                $el.style.removeProperty('--color-accent');
                $el.style.removeProperty('--color-accent-content');
                $el.style.removeProperty('--color-accent-foreground');
                return;
            }

            $el.style.setProperty('--color-accent', color);
            $el.style.setProperty('--color-accent-content', color);

            const hex = color.replace('#', '');
            const red = Number.parseInt(hex.slice(0, 2), 16);
            const green = Number.parseInt(hex.slice(2, 4), 16);
            const blue = Number.parseInt(hex.slice(4, 6), 16);
            const luminance = ((0.2126 * red) + (0.7152 * green) + (0.0722 * blue)) / 255;

            $el.style.setProperty('--color-accent-foreground', luminance > 0.6 ? '#111827' : '#ffffff');
        "
        @if ($organizationColor)
            style="--color-accent: {{ $organizationColor }}; --color-accent-content: {{ $organizationColor }}; --color-accent-foreground: {{ $accentForeground }};"
        @endif
        class="min-h-screen bg-white dark:bg-zinc-800"
    >
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('season.index')" :current="request()->routeIs('season.index')" wire:navigate>
                        {{ __('Seasons') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav heading="Resources">
                <flux:sidebar.group :heading="__('Organization')" class="grid">
                    <flux:sidebar.item
                        icon="cog-8-tooth"
                        :href="$hasOrganization ? route('organization.settings') : null"
                        :wire:navigate="$hasOrganization"
                        :class="! $hasOrganization ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''"
                    >
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
        @persist('toast')
        <flux:toast />
        @endpersist
    </body>
</html>
