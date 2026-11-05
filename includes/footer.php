<footer class="site-footer">
  <div class="container">
    <div class="newsletter">
      <p>Join our circle: stories, scents, and soulful drops.</p>
      <form action="<?= BASE_URL ?>/subscribe.php" method="post" class="newsletter-form">
        <input type="email" name="email" placeholder="Your email" required>
        <button type="submit" class="btn">Subscribe</button>
      </form>
    </div>

    <div class="footer-links">
      <a href="mailto:hello@roohveere.com">hello@roohveere.com</a>
      <a href="https://www.instagram.com/roohveere/" target="_blank" rel="noopener">Instagram</a>
      <a href="https://www.facebook.com/people/Rooh-Veere/61577770144355/" target="_blank" rel="noopener">Facebook</a>
      <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hello%20Rooh%20Veere" target="_blank" rel="noopener">WhatsApp</a>
    </div>

    <p class="copyright">© <?= date('Y') ?> <?= e(SITE_NAME) ?> By Sirishti Chhabria. All rights reserved.</p>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html> 