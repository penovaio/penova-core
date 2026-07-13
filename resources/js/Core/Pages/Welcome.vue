<script setup>
/**
 * Core - public landing page at "/". Introduces Penova Core and its tagline.
 * Shown to everyone; the primary CTA adapts to auth state
 * (Workspace when signed in, login otherwise). Uses the Penova brand palette
 * (@theme: brand / accent / sand). Self-contained (own full-screen shell).
 *
 * Locale-neutral (RFC-005 / D-027 / D-AUDIT-006): all copy renders in the active
 * locale via useI18n - English base, Persian when APP_LOCALE=fa - instead of the
 * former Persian-hardcoded layout. Page direction follows <html dir> from the
 * locale; only command snippets and proper nouns stay LTR.
 */
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useWorkspacePath } from '@/Core/composables/workspacePath';
import { useI18n } from '@/Core/composables/i18n';

const { t } = useI18n();

const user = computed(() => usePage().props.auth?.user);

const branding = computed(() => usePage().props.branding ?? {});
const logoUrl = computed(() => branding.value.logo_url || '/penova-logo.png');
const brandName = computed(() => branding.value.name || 'Penova Core');
const footerText = computed(
    () => branding.value.footer_text || 'Penova Core · The shared foundation for modular Laravel products',
);

// Guest -> login; signed-in -> straight to the Workspace (no login round-trip),
// built from the configured Workspace prefix (shared prop).
const ws = useWorkspacePath();
const panelHref = computed(() => (user.value ? ws() : '/login'));

const repoUrl = 'https://github.com/penovaio/penova-core';

// Keys into the ui.welcome catalog; copy itself lives in lang/{en,fa}/ui.php.
const features = ['auth', 'workspace', 'services', 'architecture'];
// Illustrative capability categories a Module can add - not specific or regional
// product names (Core names no Module; D-026 / D-007).
const modules = ['commerce', 'messaging', 'payments'];
const philosophy = ['small', 'share', 'build'];
</script>

<template>
    <Head title="Penova Core" />

    <div class="min-h-screen bg-sand-50 text-sand-700">
        <div class="mx-auto max-w-5xl px-6 py-16 sm:py-24">

            <!-- Hero -->
            <section class="text-center">
                <img
                    :src="logoUrl"
                    :alt="brandName"
                    width="128"
                    height="128"
                    class="mx-auto h-28 w-28 sm:h-32 sm:w-32"
                />

                <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-sand-900 sm:text-5xl">
                    {{ brandName }}
                </h1>
                <p class="mt-2 text-lg font-medium text-sand-600">{{ t('shell.tagline') }}</p>

                <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-sand-700">
                    {{ t('welcome.hero_intro') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <Link
                        :href="panelHref"
                        class="rounded-lg bg-brand px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-hover"
                    >
                        {{ t('welcome.cta_workspace') }}
                    </Link>
                    <a
                        :href="repoUrl"
                        target="_blank"
                        rel="noopener"
                        class="rounded-lg border border-brand px-5 py-2.5 text-sm font-bold text-brand hover:bg-brand/5"
                    >
                        {{ t('welcome.cta_github') }}
                    </a>
                </div>
            </section>

            <!-- Get started -->
            <section class="mt-20 text-center">
                <h2 class="text-2xl font-bold text-sand-900">{{ t('welcome.get_started_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-sand-600">
                    {{ t('welcome.get_started_body') }}
                </p>
                <div class="mx-auto mt-6 max-w-md rounded-lg bg-sand-900 px-4 py-3 text-start font-mono text-sm" dir="ltr">
                    <span class="text-accent">$</span>
                    <span class="text-sand-300"> php artisan penova:setup</span>
                </div>
            </section>

            <!-- Shared by every product -->
            <section class="mt-20 text-center">
                <h2 class="text-2xl font-bold text-sand-900">{{ t('welcome.shared_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-base font-medium text-sand-700">{{ t('welcome.shared_lead') }}</p>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-sand-600">{{ t('welcome.shared_body') }}</p>
            </section>

            <!-- What's in the Core -->
            <section class="mt-20">
                <h2 class="text-center text-2xl font-bold text-sand-900">{{ t('welcome.features_heading') }}</h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="key in features"
                        :key="key"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <h3 class="flex items-center gap-2 text-base font-bold text-sand-900">
                            <span class="text-accent">✓</span>
                            {{ t(`welcome.features.${key}.title`) }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ t(`welcome.features.${key}.desc`) }}</p>
                    </article>
                </div>
            </section>

            <!-- Extend with Modules -->
            <section class="mt-20">
                <h2 class="text-center text-2xl font-bold text-sand-900">{{ t('welcome.modules_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-center text-base font-medium text-sand-700">{{ t('welcome.modules_lead') }}</p>
                <p class="mx-auto mt-3 max-w-2xl text-center text-sm leading-relaxed text-sand-600">
                    {{ t('welcome.modules_intro') }}
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <article
                        v-for="key in modules"
                        :key="key"
                        class="rounded-xl border border-sand-300 bg-white p-5"
                    >
                        <h3 class="text-base font-bold text-sand-900">{{ t(`welcome.modules.${key}.title`) }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-sand-600">{{ t(`welcome.modules.${key}.desc`) }}</p>
                    </article>
                </div>
            </section>

            <!-- Design philosophy -->
            <section class="mt-20 text-center">
                <h2 class="text-2xl font-bold text-sand-900">{{ t('welcome.philosophy_heading') }}</h2>
                <ul class="mx-auto mt-6 flex max-w-xl flex-col gap-3">
                    <li
                        v-for="key in philosophy"
                        :key="key"
                        class="text-lg font-medium text-sand-800"
                    >
                        {{ t(`welcome.philosophy.${key}`) }}
                    </li>
                </ul>
            </section>

            <!-- Footer -->
            <footer class="mt-20 border-t border-sand-200 pt-6 text-center">
                <p class="text-xs text-sand-600">{{ footerText }}</p>
            </footer>

        </div>
    </div>
</template>
