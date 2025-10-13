(function ($) {
  "use strict";

  // Copyright year
  document.getElementById("year").textContent = new Date().getFullYear();

  // Spinner (page loader)
  var spinner = function () {
    setTimeout(function () {
      if ($("#spinner").length > 0) {
        $("#spinner").removeClass("show");
      }
    }, 500);
  };

  $(window).on("load", spinner);

  // WOW animation
  new WOW().init();

  // Sticky Navbar
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
      $(".sticky-top").addClass("shadow-sm").css("top", "0px");
    } else {
      $(".sticky-top").removeClass("shadow-sm").css("top", "-100px");
    }
  });

  // Time updater
  function updateTime() {
    const days = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    const now = new Date();
    const dayName = days[now.getDay()];
    const hours = now.getHours();
    const minutes = now.getMinutes();
    const ampm = hours >= 12 ? "PM" : "AM";
    const formattedHours = hours % 12 || 12;
    const formattedMinutes = minutes.toString().padStart(2, "0");

    $("#day").text(`${dayName} - `);
    $("#time").text(`${formattedHours}:${formattedMinutes} ${ampm}`);
  }

  $(document).ready(function () {
    setInterval(updateTime, 1000);
    updateTime();
  });

  // Back to top
  $(window).scroll(function () {
    if ($(this).scrollTop() > 300) $(".back-to-top").fadeIn("slow");
    else $(".back-to-top").fadeOut("slow");
  });

  $(".back-to-top").click(function () {
    $("html, body").animate({ scrollTop: 0 }, 1500, "easeInOutExpo");
    return false;
  });

  // Facts counter
  $('[data-toggle="counter-up"]').counterUp({
    delay: 10,
    time: 2000,
  });

  // Header carousel
  $(".header-carousel").owlCarousel({
    autoplay: true,
    smartSpeed: 1500,
    items: 1,
    dots: true,
    loop: true,
    nav: true,
    navText: [
      '<i class="bi bi-chevron-left"></i>',
      '<i class="bi bi-chevron-right"></i>',
    ],
  });

  // Testimonials carousel
  $(".testimonial-carousel").owlCarousel({
    autoplay: true,
    smartSpeed: 1000,
    center: true,
    dots: false,
    loop: true,
    nav: true,
    navText: [
      '<i class="bi bi-arrow-left"></i>',
      '<i class="bi bi-arrow-right"></i>',
    ],
    responsive: {
      0: { items: 1 },
      768: { items: 2 },
    },
  });

  // Portfolio filter
  var portfolioIsotope = $(".portfolio-container").isotope({
    itemSelector: ".portfolio-item",
    layoutMode: "fitRows",
  });

  $("#portfolio-flters li").on("click", function () {
    $("#portfolio-flters li").removeClass("active");
    $(this).addClass("active");
    portfolioIsotope.isotope({ filter: $(this).data("filter") });
  });

  // 🧩 Reusable Loading Helper
  function setButtonLoading(button, text, restoreText = null) {
    const original = restoreText || button.innerHTML;
    button.dataset.originalText = original;
    button.disabled = true;
    button.innerHTML = `${text} <span class="spinner-border spinner-border-sm"></span>`;
  }

  function resetButton(button) {
    button.disabled = false;
    button.innerHTML = button.dataset.originalText || "Submit";
  }

  // 🟢 Paystack (Card payment)
  const payBtn = document.getElementById("paystackBtn");
  if (payBtn) {
    payBtn.addEventListener("click", async function () {
      const form = document.getElementById("donation-form");
      const data = {
        name: form.name.value,
        email: form.email.value,
        phone: form.phone.value,
        amount: form.amount.value,
        currency: form.currency.value,
      };

      if (!data.name || !data.email || !data.amount) {
        alert("Please fill in your name, email, and amount before continuing.");
        return;
      }

      setButtonLoading(payBtn, "Redirecting to Paystack...");

      try {
        const response = await fetch("initialize_payment.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.status && result.data?.authorization_url) {
          window.location.href = result.data.authorization_url;
        } else {
          alert("Failed to initialize payment. Please try again.");
          resetButton(payBtn);
        }
      } catch (err) {
        console.error(err);
        alert("Something went wrong initializing the payment.");
        resetButton(payBtn);
      }
    });
  }

  // 🟢 Donation form (Normal submit)
  const donationForm = document.getElementById("donation-form");
  if (donationForm) {
    donationForm.addEventListener("submit", function () {
      const btn = document.getElementById("donateBtn");
      if (btn) setButtonLoading(btn, "Processing...");
    });
  }

  // 🟢 Newsletter
  const newsletterBtn = document.getElementById("sign-up");
  if (newsletterBtn) {
    newsletterBtn.addEventListener("click", function () {
      setButtonLoading(newsletterBtn, "Subscribing...");
      setTimeout(() => {
        alert("✅ You’ve been subscribed!");
        resetButton(newsletterBtn);
      }, 2000);
    });
  }

  // 🟢 Contact Form
  const contactForm = document.querySelector("form[action='send_mail.php']");
  if (contactForm) {
    contactForm.addEventListener("submit", function () {
      const btn = document.getElementById("btn-message");
      if (btn) setButtonLoading(btn, "Sending...");
    });
  }
})(jQuery);
