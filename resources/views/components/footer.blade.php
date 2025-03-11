<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-logo">
                <span class="logo-text">Siede<span class="highlight">Studios</span></span>
                <p class="footer-tagline">Crafting digital experiences & gaming content</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-nav">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('services') }}">Services</a></li>
                        <li><a href="{{ route('portfolio') }}">Portfolio</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-services">
                    <h4>Services</h4>
                    <ul>
                        <li><a href="{{ route('services') }}#web-development">Web Development</a></li>
                        <li><a href="{{ route('services') }}#ui-ux-design">UI/UX Design</a></li>
                        <li><a href="{{ route('services') }}#lua-development">Lua Development</a></li>
                        <li><a href="{{ route('services') }}#gmod-addons">GMod Addons</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><a href="mailto:info@siedestudios.com">info@siedestudios.com</a></li>
                        <li><a href="tel:+15551234567">+1 (555) 123-4567</a></li>
                    </ul>
                    <div class="social-links">
                        <a href="https://twitter.com/siedestudios" aria-label="Twitter" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                            </svg>
                        </a>
                        <a href="https://github.com/siedestudios" aria-label="GitHub" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                            </svg>
                        </a>
                        <a href="https://discord.gg/siedestudios" aria-label="Discord" class="social-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12Z"></path>
                                <path d="M7 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                                <path d="M15 10.5v-1c0-.3.3-.5.6-.5h.8c.3 0 .6.2.6.5v1c0 .3-.3.5-.6.5h-.8c-.3 0-.6-.2-.6-.5Z"></path>
                                <path d="M8 15h8"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} SiedeStudios. All rights reserved.</p>
            <div class="footer-legal">
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>