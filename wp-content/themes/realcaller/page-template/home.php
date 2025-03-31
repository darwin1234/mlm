<?php 
/*
    Template Name: Home Page

*/
get_header();
?>

<style>
#header{
  position:relative;
}
#vapi-icon-container{
  padding: 0!important;
  margin:  0!important;
  border-radius:  0!important;
  visibility:hidden;
}
.vapi-btn {
    border-radius: 50%;
    min-width: 50px;
    height: 50px;
    color: white;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
    display: flex;
    text-align: left;
    align-items: center;
    position: relative;
    padding: 0;
    animation: bounce 2s ease-in-out infinite;
    bottom: 0!important;
    left: 0!important;
    margin-left: 0!important;
    margin: 0;
    visibility:hidden;
}
#custom-vapi-button{
    display:block;
    width:140px;
    height:140px;
    background:url(<?php echo bloginfo('template_url');?>/assets/images/default-hover.png);
    margin:auto;
    background-size:100%;
}
#custom-vapi-button:hover {
  width:140px;
  height:140px;
  background:url(<?php echo bloginfo('template_url');?>/assets/images/active-hover.png);
  margin:auto;
  background-size:100%;
}
</style>

<div id="header" class="p-5">
      <div class="container-fluid py-5">
        <div  class="row align-items-md-stretch">
        <div class="col-md-12">
          <h1 class="text-center text-white">Welcome to RealCaller AI!</h1>
          <h2 class="text-center text-white" style="display:none">Talk to Lisa</h2>
          <a href="#" id="custom-vapi-button" style="display:none">
            <span class="microphone"></span>   
          </a>
          <script>
            var vapiInstance = null;
            const assistant = "ec1032e5-7bf8-4e69-a165-f77efed94588";
            const apiKey = "4712e393-1100-4981-813a-62981dba89a3";
            const buttonConfig = {
              target: "#custom-vapi-button",
              buttonText: "Call Vapi Assistant",
              buttonStyle: {
                backgroundColor: "#5c6bc0",
                color: "white",
                borderRadius: "8px",
                padding: "14px 30px",
                fontSize: "16px",
                fontWeight: "600",
                cursor: "pointer",
                transition: "all 0.3s ease",
                outline: "none",
                display: "inline-flex",
                alignItems: "center",
                justifyContent: "center",
              },
              buttonHoverStyle: {
                backgroundColor: "#3949ab",
                transform: "translateY(-2px)",
                boxShadow: "0 4px 12px rgba(92, 107, 192, 0.3)",
              },
            };

            (function (d, t) {
              var g = document.createElement(t),
                s = d.getElementsByTagName(t)[0];
              g.src =
                "https://cdn.jsdelivr.net/gh/VapiAI/html-script-tag@latest/dist/assets/index.js";
              g.defer = true;
              g.async = true;
              s.parentNode.insertBefore(g, s);
              g.onload = function () {
                vapiInstance = window.vapiSDK.run({
                  apiKey: apiKey,
                  assistant: assistant,
                  config: buttonConfig,
                });
              };
            })(document, "script");
         </script>
          <p class="text-center text-white" style="display:none">Ready to Transform Your Business with RealCaller AI?</p>
          <p class="text-center text-white">Join Our Free Demo and Discover How to Automate Your Business Communication—No Tech Expertise Needed!</p>
          <br><br>
          <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal"><img src="<?php echo bloginfo('template_url');?>/assets/images/book-now-2.png" class="d-block mx-auto"></a>
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
 <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="float:right; margin:5px; background:#fff; padding:5px; font-size:20px;">x</button>
      <div class="modal-body" height="1000" style="overflow:hidden;">
          <iframe src="https://api.marketingdpt.co/widget/form/kHHLGuGQ9R8B8PPl31En?notrack=true" width="100%" height="1000" frameborder="0" scrolling="auto"></iframe>
      </div>
    </div>
  </div>
</div> 
<?php  get_footer(); ?>