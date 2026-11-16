<script setup>
import { Head, Link } from '@inertiajs/vue3';
import LegalPage from '@/components/legal/LegalPage.vue';
import BusinessDetails from '@/components/legal/BusinessDetails.vue';

const props = defineProps({ business: Object });

const sections = [
    { id: 'about', title: 'About these terms' },
    { id: 'using-the-site', title: 'Using this website' },
    { id: 'ip', title: 'Intellectual property' },
    { id: 'third-parties', title: 'Third-party services' },
    { id: 'portal', title: 'Client portal' },
    { id: 'services', title: 'Our services' },
    { id: 'payment', title: 'Quotes and payment' },
    { id: 'ownership', title: 'Who owns the work' },
    { id: 'consumers', title: 'If you are a consumer' },
    { id: 'liability', title: 'Our liability' },
    { id: 'ending', title: 'Ending a project' },
    { id: 'general', title: 'General' },
];
</script>

<template>
    <Head title="Terms" />
    <LegalPage eyebrow="Legal" title="Terms of use" :updated="business.updated" :sections="sections" intro="The rules for using this website, and the terms that apply when you hire us — in plain English.">
        <h2 id="about" data-legal-section>About these terms</h2>
        <p>These terms are between you and {{ business.trading_name }}<template v-if="business.legal_name">, the trading name of {{ business.legal_name }}</template> ("we", "us"). Part one covers using this website. Part two covers the design and development work we do for clients. By using the site you agree to part one. Part two applies when you hire us, unless we've agreed different terms with you in writing — if so, the written agreement wins where the two differ.</p>
        <BusinessDetails :business="business" />

        <h2 id="using-the-site" data-legal-section>Using this website</h2>
        <p>You're welcome to browse, read and share links to anything here. When you use the site, including its forms and the client portal, please don't:</p>
        <ul>
            <li>break the law, or use the site to harass, defraud or mislead anyone;</li>
            <li>try to get into areas, accounts or data that aren't yours, or test the site's security without our written permission;</li>
            <li>upload viruses or other harmful code, or send spam through our forms;</li>
            <li>scrape or overload the site in a way that slows it down for others.</li>
        </ul>
        <p>We work hard to keep the site accurate and available, but it's provided "as is". We may change or withdraw any part of it, and we don't promise it will always be uninterrupted or error-free. Prices and descriptions on the site are an invitation to get in touch, not a binding offer — your quote sets out what's actually agreed.</p>

        <h2 id="ip" data-legal-section>Intellectual property</h2>
        <p>The design, code, text, graphics and logo of this website belong to us. Screenshots and project material in case studies belong to us or our clients and are shown with permission. Other names and logos (such as Garry's Mod, GmodStore, Discord and PayPal) belong to their owners. You may not copy or reuse our content for commercial purposes without our written permission.</p>

        <h2 id="third-parties" data-legal-section>Third-party services</h2>
        <p>The site links to and works with other services — GmodStore for addon purchases, PayPal for payments and Discord for sign-in. Their own terms and privacy policies apply when you use them, and we aren't responsible for their content or availability.</p>
        <p><strong>Addons sold on GmodStore</strong> are bought from and licensed through GmodStore. GmodStore's terms of service and the product's licence govern those purchases, including refunds; these terms don't change them.</p>

        <h2 id="portal" data-legal-section>Client portal</h2>
        <p>Portal access is for our clients and the people they invite. Keep your Discord account secure, and tell us straight away if you think someone else has accessed your portal. Only upload files you have the right to share with us. We can suspend portal access if these terms are broken, or close it when our work together ends — we'll give you a chance to download your files first.</p>

        <h2 id="services" data-legal-section>Our services</h2>
        <p>We provide web development, interface design and game-server (Garry's Mod / Lua) development. Every project starts with a written quote setting out the scope, price and expected timeline. We'll do the work with reasonable care and skill, keep you updated, and tell you promptly about anything likely to affect the timeline.</p>
        <p>To keep things moving, you agree to give us the content, feedback, access and decisions we need in good time. If delays on your side hold up the project, the timeline will move accordingly. You confirm that anything you send us — text, images, logos, code — is yours to use or that you have permission to use it.</p>
        <p>Changes beyond the agreed scope are welcome. We'll tell you if a change affects the price or timeline, and won't go ahead with extra paid work until you've agreed.</p>
        <p>After launch, we fix bugs in our own work that you report within 30 days at no extra cost. Ongoing support, maintenance and new features are quoted separately.</p>

        <h2 id="payment" data-legal-section>Quotes and payment</h2>
        <ul>
            <li><strong>Quotes</strong> are valid for 30 days. A contract is formed when you accept a quote in writing (email or Discord message is fine) and we confirm.</li>
            <li><strong>Invoices</strong> are paid through the secure link we send you, via PayPal. Payment is due by the date on the invoice. Larger projects are usually split into a deposit and milestone payments, as set out in your quote.</li>
            <li><strong>Prices</strong> are in the currency on your invoice.<template v-if="business.vat_number"> VAT is added where it applies.</template><template v-else> We are not currently VAT registered, so no VAT is charged.</template></li>
            <li><strong>Late payment:</strong> if an invoice is overdue we may pause work until it's paid. For business clients, we may also claim interest and compensation under the Late Payment of Commercial Debts (Interest) Act 1998.</li>
        </ul>

        <h2 id="ownership" data-legal-section>Who owns the work</h2>
        <p>Once you've paid in full, you own the bespoke work we created specifically for your project — the designs, code and content made for you — and can use, change and build on it however you like.</p>
        <p>We keep ownership of our own pre-existing tools, libraries and frameworks (for example, shared code we use across projects). Where they're part of your project, you get a permanent, non-exclusive licence to use them as part of that project. Third-party software keeps its own licence terms.</p>
        <p>We may show the finished work in our portfolio and case studies. If you'd rather we didn't, or there's anything confidential, just tell us and we'll respect that.</p>

        <h2 id="consumers" data-legal-section>If you are a consumer</h2>
        <p>Most of our clients are businesses. If you hire us as an individual, for purposes outside your trade, business or profession, you are a consumer and have extra legal rights. Nothing in these terms affects them.</p>
        <ul>
            <li><strong>Your right to cancel:</strong> under the Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013, you can cancel within 14 days of the contract being formed, without giving a reason. To cancel, email <a :href="`mailto:${business.contact_email}`">{{ business.contact_email }}</a> with a clear statement that you want to cancel.</li>
            <li><strong>If you ask us to start sooner:</strong> we'll only begin work within the 14 days if you expressly ask us to. If you then cancel within the 14 days, you'll pay a fair amount for the work done up to that point. If the work is fully completed within the 14 days at your request, the right to cancel no longer applies.</li>
            <li><strong>Refunds</strong> are made within 14 days of cancelling, to the payment method you used.</li>
            <li><strong>Your statutory rights:</strong> under the Consumer Rights Act 2015, our services must be performed with reasonable care and skill, and anything we've told you about them that you relied on is binding. If something isn't right, you can ask us to fix it or, if that isn't possible, to reduce the price. Citizens Advice can explain your rights in more detail.</li>
        </ul>

        <h2 id="liability" data-legal-section>Our liability</h2>
        <p>Nothing in these terms limits or excludes our liability for death or personal injury caused by our negligence, for fraud or fraudulent misrepresentation, or for anything else the law doesn't allow us to limit.</p>
        <p><strong>Business clients:</strong> we aren't liable for loss of profits, revenue, business, data or goodwill, or for any indirect or consequential loss. Our total liability arising from a project is limited to the total amount you paid us for that project in the 12 months before the claim. Please keep your own backups of anything important.</p>
        <p><strong>Consumers:</strong> we're responsible for loss or damage you suffer that is a foreseeable result of us breaking these terms or failing to use reasonable care and skill. We aren't responsible for losses that weren't foreseeable, or for business losses — our services are provided to you for private use.</p>
        <p>We aren't responsible for delays or failures caused by events outside our reasonable control (for example, outages at a hosting or payment provider). We'll let you know and do what we reasonably can to limit the impact.</p>

        <h2 id="ending" data-legal-section>Ending a project</h2>
        <p>Either of us can end a project by giving written notice. You'll pay for work completed and any costs we've committed to up to that point, and we'll hand over the work you've paid for. We may end a project straight away if an invoice is more than 30 days overdue, or if you seriously break these terms. Consumers also have the cancellation rights described above.</p>
        <p>We both keep each other's confidential information private, during the project and afterwards, unless the law requires it to be shared.</p>

        <h2 id="general" data-legal-section>General</h2>
        <ul>
            <li><strong>Changes:</strong> we may update these terms. The version on this page when you accept a quote applies to that project.</li>
            <li><strong>Transfer:</strong> we may transfer our rights under these terms to another business, but we'll make sure your rights aren't affected.</li>
            <li><strong>Third parties:</strong> nobody other than you and us has any right to enforce these terms.</li>
            <li><strong>Severability:</strong> if a court decides part of these terms can't be enforced, the rest still applies.</li>
            <li><strong>Waiver:</strong> if we don't enforce a right straight away, we can still enforce it later.</li>
            <li><strong>Law and courts:</strong> these terms are governed by the law of {{ business.jurisdiction }}, and the courts of {{ business.jurisdiction }} will deal with any dispute. If you're a consumer living elsewhere in the UK, you can also bring proceedings in your local courts.</li>
        </ul>
        <p>Questions? Email <a :href="`mailto:${business.contact_email}`">{{ business.contact_email }}</a>. How we handle your data is explained in our <Link href="/privacy">privacy policy</Link>.</p>
    </LegalPage>
</template>
