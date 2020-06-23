// var bFullHover = ! window.matchMedia("(hover:none), (hover:on-demand)").matches; // touchscreen without mouse gives false
// ============================================================================
window.onload = function(){
// ============================================================================
  // if (bFullHover){
    showHide('block1', false);
    showHide('block2', false);
    showHide('block3', false);
    showHide('block4', false);
  // } else {
  //   showHide('block1', true);
  //   showHide('block2', true);
  //   showHide('block3', true);
  //   showHide('block4', true);
  // }
};

// ============================================================================
function showHide(tElemName, bShow){
// ============================================================================
  elem = document.getElementById(tElemName);
  if (elem){
    if (bShow) {
      elem.style.display = "block";
      // elem.style.height = "auto";
    } else {
      elem.style.display = "none";
      // elem.style.height = 0;
    }
  }
}

// ============================================================================
function showHide2(tElemName){
// ============================================================================
  // alert(tElemName);
  elem = document.getElementById('block' + tElemName);
  button = document.getElementById('button' + tElemName);
  if (elem.style.display === "none") {
    // $('#block1').slideDown("slow");
    $('#' + 'block' + tElemName).slideDown("slow");
    button.value = "Less";
  } else {
    // $('#block1').slideUp("slow");
    $('#' + 'block' + tElemName).slideUp("slow");
    button.value = "More";
  }
}
