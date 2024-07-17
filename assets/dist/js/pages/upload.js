var dropZone = document.querySelector('#drop-zone');
var imageUpload = document.querySelector('#image-upload');
var browseButton = document.querySelector('#browse-button');
var uploadButton = document.querySelector('#upload-button');
var clearButton = document.querySelector('#clear-button');
var progressBar = dropZone.querySelector('.progress-bar');

dropZone.addEventListener('dragover', function(event) {
  event.preventDefault();
  dropZone.classList.add('drag-over');
});

dropZone.addEventListener('dragleave', function(event) {
  dropZone.classList.remove('drag-over');
});

dropZone.addEventListener('drop', function(event) {
  event.preventDefault();
  dropZone.classList.remove('drag-over');
  imageUpload.files = event.dataTransfer.files;
  dropZone.classList.add('has-thumbnail');
  var reader = new FileReader();
  reader.onload = function(event) {
    dropZone.querySelector('.thumbnail').src = event.target.result;
  };
  reader.readAsDataURL(imageUpload.files[0]);
});

browseButton.addEventListener('click', function(event) {
  imageUpload.click();
});

clearButton.addEventListener('click', function(event) {
  console.log(1);
  progressBar.parentNode.classList.remove("is-uploading");
  progressBar.setAttribute('aria-valuenow', 0);
  progressBar.style.width = `0%`;
  progressBar.innerHTML = `0%`;

  uploadButton.removeAttribute('disabled', '');

  imageUpload.value = '';
  dropZone.classList.remove('has-thumbnail');
  dropZone.querySelector('.thumbnail').src = '';
});

uploadButton.addEventListener('click', function(event) {
  var formData = new FormData();
  formData.append('image', imageUpload.files[0]);

  progressBar.parentNode.classList.add("is-uploading");
  uploadButton.setAttribute('disabled', '');

  var xhr = new XMLHttpRequest();
  xhr.upload.addEventListener('progress', async function(event) {
    await sleep(1000);
    var progress = Math.floor((event.loaded / event.total) * 100);
    progressBar.setAttribute('aria-valuenow', progress);
    progressBar.style.width = `${progressBar.getAttribute('aria-valuenow')}%`;
    progressBar.innerHTML = `${progressBar.getAttribute('aria-valuenow')}%`;
  });

  xhr.addEventListener('load', async function() {
    const responseCode = xhr.status;
    const responseMessage = JSON.parse(xhr.responseText);
    if (responseCode !== 201) {
      clearButton.reset();
      
      progressBar.setAttribute('aria-valuenow', 99);
      progressBar.style.width = `99%`;
      progressBar.innerHTML = `99%`;
      dropZone.innerHTML += `<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
        ${responseMessage.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>`;
    }
    else {
      await sleep(1000);
      window.location.replace("/edit");
    }
  });

  xhr.open('POST', '/api/upload');
  xhr.send(formData);
});

imageUpload.addEventListener('change', function(event) {
  if (imageUpload.files.length === 0 || !imageUpload.files[0].type.startsWith('image/')) {
    dropZone.classList.remove('has-thumbnail');
  } else {
    dropZone.classList.add('has-thumbnail');
    var reader = new FileReader();
    reader.onload = function(event) {
      dropZone.querySelector('.thumbnail').src = event.target.result;
    };
    reader.readAsDataURL(imageUpload.files[0]);
  }
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}