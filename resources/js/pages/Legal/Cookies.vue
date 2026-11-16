<script setup>
import { Head } from '@inertiajs/vue3';
import { Cookie } from 'lucide-vue-next';
import LegalPage from '@/components/legal/LegalPage.vue';

defineProps({ business: Object, analytics: Boolean, cookies: Array });

const sections = [
    { id: 'what-are-cookies', title: 'What cookies are' },
    { id: 'cookies-we-use', title: 'Cookies we use' },
    { id: 'your-choices', title: 'Your choices' },
    { id: 'contact', title: 'Questions' },
];

const openSettings = () => window.dispatchEvent(new CustomEvent('cookie-settings'));
</script>

<template>
    <Head title="Cookie policy" />
    <LegalPage eyebrow="Legal" title="Cookie policy" :updated="business.updated" :sections="sections" intro="Which cookies this site sets, what they do, and how you can control them.">
        <h2 id="what-are-cookies" data-legal-section>What cookies are</h2>
        <p>Cookies are small text files a website stores on your device. Under the Privacy and Electronic Communications Regulations (PECR), we can only set cookies that are <strong>strictly necessary</strong> for the site to work without asking you. Anything else needs your consent first.</p>

        <h2 id="cookies-we-use" data-legal-section>Cookies we use</h2>
        <p v-if="!analytics"><strong>This site only uses strictly necessary cookies.</strong> There are no analytics, advertising or tracking cookies, which is why you won't see a cookie banner.</p>
        <p v-else>Strictly necessary cookies are always on. Analytics cookies are only set if you choose to accept them, and you can change your mind at any time.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Cookie</th><th>Purpose</th><th>Lasts</th><th>Type</th></tr></thead>
                <tbody>
                    <tr v-for="c in cookies" :key="c.name">
                        <td><code>{{ c.name }}</code></td>
                        <td>{{ c.purpose }}</td>
                        <td>{{ c.duration }}</td>
                        <td>{{ c.type }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>We don't use local storage or any other technology to track you across websites.</p>

        <h2 id="your-choices" data-legal-section>Your choices</h2>
        <template v-if="analytics">
            <p>You can accept or reject analytics cookies at any time. Rejecting them doesn't affect how the site works.</p>
            <p><button type="button" class="btn btn-glass btn-sm" @click="openSettings"><Cookie :size="15" /> Change cookie settings</button></p>
        </template>
        <p>You can also block or delete cookies in your browser settings. If you block strictly necessary cookies, forms and signing in to the client portal won't work. The ICO's guide at <a href="https://ico.org.uk/for-the-public/online/cookies/" target="_blank" rel="noopener">ico.org.uk</a> explains how cookies work and how to control them.</p>

        <h2 id="contact" data-legal-section>Questions</h2>
        <p>Email <a :href="`mailto:${business.email}`">{{ business.email }}</a>. More about how we handle personal data is in our privacy policy.</p>
    </LegalPage>
</template>
