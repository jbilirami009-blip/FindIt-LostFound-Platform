<?php
$page_title = 'Contact';
$current_page = 'contact';
require_once 'includes/header.php';
?>

  <!-- Contact -->
  <section class="contact container">
    <h1>Get In Touch</h1>
    <p class="subtitle">Have questions or feedback? We'd love to hear from you!</p>

    <div class="contact-grid">
      <div class="card">
        <h3>Send us a message</h3>
        <form>
          <label>Name</label>
          <input type="text" placeholder="Your name" required>
          <label>Email</label>
          <input type="email" placeholder="your.email@example.com" required>
          <label>Message</label>
          <textarea rows="4" placeholder="Your message..." required></textarea>
          <button type="submit">✈️ Send Message</button>
        </form>
      </div>

      <div class="card">
        <h3>Connect with us</h3>
        <ul class="info-list">
          <li>📧 <b>Email</b><br>contact@findit.com</li>
          <li>💻 <b>GitHub</b><br>View our projects</li>
          <li>🔗 <b>LinkedIn</b><br>Connect professionally</li>
        </ul>
      </div>
    </div>

    <div class="card office">
      <h3>Office Hours</h3>
      <p>We're available Monday–Friday, 9:00 AM – 5:00 PM CET</p>
    </div>
  </section>

<?php require_once 'includes/footer.php'; ?>

