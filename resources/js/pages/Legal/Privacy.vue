<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LegalPage from '@/components/legal/LegalPage.vue';
import BusinessDetails from '@/components/legal/BusinessDetails.vue';

const props = defineProps({ business: Object, analytics: Boolean, discord_alerts: Boolean, retention: Object });

const sections = [
    { id: 'who-we-are', title: 'Who we are' },
    { id: 'summary', title: 'The short version' },
    { id: 'what-we-collect', title: 'What we collect and why' },
    { id: 'sharing', title: 'Who we share it with' },
    { id: 'transfers', title: 'International transfers' },
    { id: 'security', title: 'Keeping it safe' },
    { id: 'rights', title: 'Your rights' },
    { id: 'complaints', title: 'Complaints' },
    { id: 'children', title: 'Children' },
    { id: 'changes', title: 'Changes to this policy' },
];
</script>

<template>
    <Head title="Privacy policy" />
    <LegalPage eyebrow="Legal" title="Privacy policy" :updated="business.updated" :sections="sections" intro="How we collect, use and protect your personal data, and the rights you have under UK data protection law.">
        <h2 id="who-we-are" data-legal-section>Who we are</h2>
        <p>
            This website is run by {{ business.trading_name }}<template v-if="business.legal_name">, the trading name of {{ business.legal_name }}</template>.
            We are the <strong>data controller</strong> for the personal data described in this policy, which means we decide how and why it is used.
            We handle it in line with the UK General Data Protection Regulation (UK GDPR), the Data Protection Act 2018 and the Privacy and Electronic Communications Regulations (PECR).
        </p>
        <BusinessDetails :business="business" />
        <p>For anything about your data, email <a :href="`mailto:${business.email}`">{{ business.email }}</a>. We don't have a Data Protection Officer — we're a small studio and aren't required to — so your message comes straight to us.</p>

        <h2 id="summary" data-legal-section>The short version</h2>
        <ul>
            <li>We only collect what we need to reply to you, do the work you've asked for, get paid and keep the site secure.</li>
            <li>We never sell your data or use it for advertising.</li>
            <li v-if="analytics">Analytics cookies are only set if you choose to accept them.</li>
            <li v-else>We don't use analytics, advertising or tracking cookies.</li>
            <li>Card details are handled by PayPal — they never reach us.</li>
            <li>You can ask to see, correct or delete your data at any time.</li>
        </ul>

        <h2 id="what-we-collect" data-legal-section>What we collect and why</h2>
        <p>Under UK GDPR we need a lawful basis for each use of your data. Here's everything we collect, why, the basis we rely on, and how long we keep it.</p>

        <h3>When you contact us</h3>
        <p>Your name, email address, Discord username (if you give it), the service and budget you pick, your message, and your IP address.</p>
        <ul>
            <li><strong>Why:</strong> to reply, discuss your project and send a quote. The IP address helps us block spam and abuse.</li>
            <li><strong>Lawful basis:</strong> taking steps at your request before entering into a contract, and our legitimate interest in answering enquiries and protecting the site.</li>
            <li><strong>Kept for:</strong> {{ retention.enquiry_months }} months, then deleted automatically. IP addresses are removed after {{ retention.enquiry_ip_days }} days. If the enquiry turns into a project, it's kept as part of your client records (below).</li>
        </ul>

        <h3>When you become a client</h3>
        <p>Your name, company, email address, project details, messages and files you share with us, and records of the work.</p>
        <ul>
            <li><strong>Why:</strong> to deliver and support your project, and to communicate with you about it.</li>
            <li><strong>Lawful basis:</strong> performing our contract with you.</li>
            <li><strong>Kept for:</strong> while we work together and for up to 6 years afterwards — the period in which a contract claim can be brought — unless you ask us to delete it sooner and we don't need it for a legal reason.</li>
        </ul>

        <h3>Client portal and signing in with Discord</h3>
        <p>If you use the client portal, you sign in with Discord. We receive your Discord user ID, username, display name and profile picture. We only ask Discord for this basic profile — never your password, email address, messages or servers.</p>
        <ul>
            <li><strong>Why:</strong> to recognise you, show your projects, invoices and files, and keep your account secure.</li>
            <li><strong>Lawful basis:</strong> performing our contract with you.</li>
            <li><strong>Kept for:</strong> while you have portal access. When a client account is closed, portal access ends and the files in it are deleted.</li>
        </ul>

        <h3>When you pay an invoice</h3>
        <p>The name, company and email on your invoice, and from PayPal: your PayPal email address, the transaction ID, amount and payment status. <strong>We never see or store your card or bank details</strong> — PayPal handles the payment itself.</p>
        <ul>
            <li><strong>Why:</strong> to take payment, match it to your invoice and keep business records.</li>
            <li><strong>Lawful basis:</strong> performing our contract with you, and our legal obligation to keep accounting records.</li>
            <li><strong>Kept for:</strong> {{ retention.financial_years }} years. HM Revenue &amp; Customs requires records to be kept for 6 years from the end of the financial year they relate to.</li>
        </ul>

        <h3>Testimonials</h3>
        <p>If you leave a review: your name, company, role, photo (if provided), review text and star rating.</p>
        <ul>
            <li><strong>Why:</strong> to publish your feedback on this website.</li>
            <li><strong>Lawful basis:</strong> your consent, which you give when you submit the review. You can withdraw it at any time by emailing us, and we'll take the review down.</li>
            <li><strong>Kept for:</strong> until you ask us to remove it. Review invitations that are never used are deleted after {{ retention.unused_invite_months }} months.</li>
        </ul>

        <h3>When you browse the site</h3>
        <p>Like any website, our server receives your IP address, browser type and the pages you request.</p>
        <ul>
            <li><strong>Why:</strong> to deliver pages, prevent abuse (for example, limiting how often a form can be sent) and fix errors.</li>
            <li><strong>Lawful basis:</strong> our legitimate interest in running a secure, working website.</li>
            <li><strong>Kept for:</strong> error logs are kept for {{ retention.log_days }} days. Rate-limit counters expire within a day. Sign-in sessions expire after inactivity.</li>
        </ul>

        <template v-if="analytics">
            <h3>Analytics</h3>
            <p>If — and only if — you accept analytics cookies, we use Google Analytics to understand how many people visit and which pages they use. It records pseudonymous identifiers, pages viewed, approximate location (country/city) and device type.</p>
            <ul>
                <li><strong>Lawful basis:</strong> your consent. You can change your mind at any time using <strong>Cookie settings</strong> in the footer.</li>
                <li><strong>Kept for:</strong> up to 14 months in Google Analytics.</li>
            </ul>
        </template>

        <h3>Things we don't collect</h3>
        <p>The status page, store and case studies don't collect personal data. On the store page, product images are loaded directly from GmodStore, so GmodStore's servers will see your IP address — see <a href="https://www.gmodstore.com/legal/privacy" target="_blank" rel="noopener">GmodStore's privacy policy</a>. We don't make decisions about you using automated processing or profiling.</p>

        <h2 id="sharing" data-legal-section>Who we share it with</h2>
        <p>We never sell your data. We only share it with these organisations, and only as far as they need it:</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Who</th><th>What for</th><th>Where</th></tr></thead>
                <tbody>
                    <tr v-if="business.hosting"><td>{{ business.hosting }}</td><td>Hosts this website and its database (our processor).</td><td>EU</td></tr>
                    <tr><td>Cloudflare, Inc.</td><td>Domain name system (DNS) and, where enabled, security and performance for the site (our processor).</td><td>Global, incl. USA</td></tr>
                    <tr><td>PayPal (Europe) S.à r.l. et Cie, S.C.A.</td><td>Processes invoice payments. PayPal is an independent controller of the data you give it — see <a href="https://www.paypal.com/uk/legalhub/privacy-full" target="_blank" rel="noopener">PayPal's privacy statement</a>.</td><td>Luxembourg / global</td></tr>
                    <tr><td>Discord Inc.</td><td>Sign-in for the client portal (independent controller of your Discord account).<template v-if="discord_alerts"> We also receive notifications about new enquiries, payments and portal activity in our own private Discord channel; these include your name, email and part of your message.</template></td><td>USA</td></tr>
                    <tr v-if="analytics"><td>Google LLC</td><td>Google Analytics, only if you accept analytics cookies (our processor).</td><td>USA</td></tr>
                    <tr><td>Our email provider</td><td>Sending and receiving email when we reply to you.</td><td>UK / EU</td></tr>
                    <tr><td>Professional advisers</td><td>Our accountant, insurers or legal advisers, where needed to run the business.</td><td>UK</td></tr>
                </tbody>
            </table>
        </div>
        <p>We may also disclose data where the law requires it — for example to HM Revenue &amp; Customs or the police — or to protect our legal rights. If the business were ever sold, client records would transfer to the new owner, who would have to keep honouring this policy.</p>

        <h2 id="transfers" data-legal-section>International transfers</h2>
        <p>Some of the services above store or process data outside the UK. When that happens, we make sure it's protected to UK standards:</p>
        <ul>
            <li><strong>European Economic Area:</strong> covered by the UK's adequacy regulations, so the same level of protection applies.</li>
            <li><strong>United States:</strong> we rely on the UK Extension to the EU–US Data Privacy Framework where the company is certified, or otherwise on the International Data Transfer Addendum approved by the ICO.</li>
        </ul>
        <p>Email us if you'd like more detail about the safeguards for a particular provider.</p>

        <h2 id="security" data-legal-section>Keeping it safe</h2>
        <ul>
            <li>The whole site is served over HTTPS, so data is encrypted in transit.</li>
            <li>Files shared through the client portal are stored privately and only ever downloaded by you or us.</li>
            <li>Only allow-listed accounts can reach the admin area, and API keys are stored encrypted.</li>
            <li>We keep the server and software up to date, and delete data once the periods above have passed.</li>
        </ul>
        <p>No system is perfectly secure. If a breach ever put your rights at risk, we'd tell you — and the ICO where required — without undue delay.</p>

        <h2 id="rights" data-legal-section>Your rights</h2>
        <p>Under UK data protection law you have the right to:</p>
        <ul>
            <li><strong>Access</strong> — get a copy of the personal data we hold about you.</li>
            <li><strong>Rectification</strong> — have inaccurate data corrected or incomplete data completed.</li>
            <li><strong>Erasure</strong> — have your data deleted, where we no longer have a reason to keep it.</li>
            <li><strong>Restriction</strong> — ask us to limit how we use your data while a concern is looked into.</li>
            <li><strong>Objection</strong> — object to us using your data based on legitimate interests.</li>
            <li><strong>Portability</strong> — receive data you gave us in a machine-readable format, or have it sent to someone else.</li>
            <li><strong>Withdraw consent</strong> — where we rely on consent (testimonials{{ analytics ? ', analytics' : '' }}), at any time. This doesn't affect anything done before you withdrew.</li>
        </ul>
        <p>To use any of these rights, email <a :href="`mailto:${business.email}`">{{ business.email }}</a>. It's free, and we'll reply within one month. We may need to confirm your identity first. Some rights have exceptions — for example, we must keep invoices for the period HMRC requires even if you ask us to delete them — and we'll explain if one applies.</p>

        <h2 id="complaints" data-legal-section>Complaints</h2>
        <p>If you're unhappy with how we've handled your data, please tell us first and we'll do our best to put it right. You also have the right to complain to the Information Commissioner's Office (ICO), the UK's data protection regulator:</p>
        <ul>
            <li>Website: <a href="https://ico.org.uk/make-a-complaint/" target="_blank" rel="noopener">ico.org.uk/make-a-complaint</a></li>
            <li>Helpline: 0303 123 1113</li>
            <li>Post: Information Commissioner's Office, Wycliffe House, Water Lane, Wilmslow, Cheshire, SK9 5AF</li>
        </ul>

        <h2 id="children" data-legal-section>Children</h2>
        <p>Our services are for businesses and adults. We don't knowingly collect data from children under 13 — the age of digital consent in the UK — and Discord sign-in is only available to users aged 13 or over. If you think a child has given us their data, email us and we'll delete it.</p>

        <h2 id="changes" data-legal-section>Changes to this policy</h2>
        <p>We'll update this page if the way we handle data changes, and change the "last updated" date at the top. If a change significantly affects how we use data you've already given us, we'll let you know directly. See our <Link href="/cookies">cookie policy</Link> for details of the cookies this site uses.</p>
    </LegalPage>
</template>
