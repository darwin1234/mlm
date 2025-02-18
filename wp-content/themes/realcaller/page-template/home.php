<?php 
/*
    Template Name: Home Page

*/
get_header();
?>


<div id="header" class="p-5">
      <div class="container-fluid py-5">
        <div  class="row align-items-md-stretch">
        <div class="col-md-12">
          <h1 class="text-center text-white">Welcome to RealCaller AI!</h1>
          <h2 class="text-center text-white">Talk to Lisa</h2>
          <img src="<?php echo bloginfo('template_url');?>/assets/images/microphone.png" class="d-block mx-auto">
          <p class="text-center text-white">Ready to Transform Your Business with RealCaller AI?</p>
          <p class="text-center text-white">Join Our Free Demo and Discover How to Automate Your Business Communication—No Tech Expertise Needed!</p>
          <img src="<?php echo bloginfo('template_url');?>/assets/images/book-now-2.png" class="d-block mx-auto">
      </div>

        </div>
      </div>
  </div> 
    <div id="section2">
      <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-center text-white">Features</h1>
        <p class="text-center text-white">Your Communication, Simplified. Your Growth, Amplified. </p>
        <div id="grid" class="container">
          <div class="row mt-5 mb-5">
            <div class="col">
              <span class="vector1"></span>
              <p class="text-white mt-4">Automated Cold Calling</p>
              <p class="text-white">Eliminate repetitive tasks and let our AI handle the calls for you.</p>
            </div>
            <div class="col">
              <span class="vector2"></span>
              <p class="text-white mt-4">Instant Lead Engagement</p>
              <p class="text-white">Engage with leads immediately to boost your conversion rates.</p>
            </div>
            <div class="col">
              <span class="vector3"></span>
              <p class="text-white mt-4">Seamless CRM Integration</p>
              <p class="text-white">Easily sync with Go High Level and other popular CRMs.</p>
            </div>
            <div class="col">
              <span class="vector4"></span>
              <p class="text-white mt-4">24/7 Availability</p>
              <p class="text-white">Your business never sleeps—RealCaller AI is always on.</p>
            </div>
          </div>
          <div class="row mt-5 mb-5 ">
            <div class="col">
              <span class="vector5"></span>
              <p class="text-white mt-4">Natural, Human-Like Conversations</p>
              <p class="text-white">AI that sounds like a real person, making your customers feel heard.</p>
            </div>
            <div class="col">
              <span class="vector6"></span>
              <p class="text-white mt-4">Real-Time Analytics</p>
              <p class="text-white">Track performance and optimize your outreach with detailed insights.</p>
            </div>
            <div class="col">
              <span class="vector7"></span>
              <p class="text-white mt-4">Customizable Scripts</p>
              <p class="text-white">Tailor the AI’s messaging to fit your brand voice.</p>
            </div>
            <div class="col">
              <span class="vector8"></span>
              <p class="text-white mt-4">Scalable for Any Business</p>
              <p class="text-white">From startups to enterprises, RealCaller AI grows with you.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php  get_footer(); ?>