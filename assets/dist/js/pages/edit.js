const aiAltImg = document.getElementById("ai-alt-img");
const aiAltLoader = document.getElementById("ai-alt-loader");
const aiAltLabel = document.getElementById("ai-alt-label");
const aiAltRefresh = document.getElementById("ai-alt-refresh");

function displayAiAlternate() {
    aiAltImg.classList.add("d-none");
    aiAltLoader.classList.remove("d-none");
    aiAltLabel.classList.add("d-none");
    aiAltRefresh.setAttribute('disabled', '');

    const url = "/api/alternate";
    fetch(url)
      .then(response => response.json())
      .then(data => {
        aiAltLoader.classList.add("d-none");
        aiAltImg.src = data.message;
        aiAltImg.classList.remove("d-none");
        aiAltRefresh.removeAttribute("disabled");
        
      })
      .catch(error => {
        console.error(error);
        aiAltLoader.classList.add("d-none");
        aiAltLabel.classList.remove("d-none");
        aiAltRefresh.removeAttribute("disabled");
      });
}

aiAltRefresh.addEventListener("click", function(){
    displayAiAlternate();
});

displayAiAlternate();