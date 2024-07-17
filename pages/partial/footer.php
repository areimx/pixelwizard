  <footer class="pt-5 my-5 text-muted border-top">
      Created by Adnan Selçuk Yalın &middot; &copy; <?php echo date("Y"); ?>
    </footer>
  </div>

  <script src="assets/dist/js/bootstrap.bundle.min.js"></script>
  <?php 
    if(file_exists("../assets/dist/js/pages/".$page.".js"))
      echo '<script src="assets/dist/js/pages/'.$page.'.js"></script>';
  ?>
</body>

</html>