<?php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);

  header("Cache-Control: no-cache, no-store, must-revalidate");
  header("Pragma: no-cache");
  header("Expires: 0");

  // Env variables
  include("./env/pcregrep.php");
  include("./env/dir-snippets.php");

  // Configurable
  $DEFAULT_THUMBNAIL_SIZE = "90x90"; // height x width
  $warningSearchWillFail_Arr = [];
?><!DOCTYPE html>
<html lang="en">
  <head>
   <title>Snippets Mastery</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <!-- jQuery and Bootstrap  -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.1.2/handlebars.min.js"></script>

    <!-- Autoexpand, Markdown (dependencies)-->
    <script src="assets/js/vendors/autoExpand/autoExpand.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/markdown-it@13.0.1/dist/markdown-it.min.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/markdown-it@12.0.4/dist/markdown-it.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/markdown-it-emoji/1.4.0/markdown-it-emoji.min.js"></script>
    <script src="https://unpkg.com/markdown-it-anchor@8.6.5/dist/markdownItAnchor.umd.js"></script>

        
    <link href="assets/css/explorer.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/multistates.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="assets/css/thermos.css?v=<?php echo time(); ?>" rel="stylesheet">


    <?php
    // TODO:
    // https://stackoverflow.com/questions/33850412/merge-javascript-objects-in-array-with-same-key
    // ggl - array of objects same keys merge

      // function rglob($pattern, $flags = GLOB_ONLYDIR) {
      //   global $warningSearchWillFail_Arr;

      //   $files = glob($pattern, $flags); 
      //   foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR) as $dir) {
      //       $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
      //       $folderName = basename($dir);
      //       if(strpos($folderName, ":")!==false || strpos($folderName, "/")!==false) {
      //         array_push($warningSearchWillFail_Arr, $folderName);
      //       }
      //       // die();
      //   }
      //   return $files;
      // } // rglob


      function rglob($pattern, $flags = 0) {
        global $warningSearchWillFail_Arr;
    
        $files = glob($pattern, $flags); 
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR) as $dir) {
            $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
            $folderName = basename($dir);
            if (strpos($folderName, ":") !== false || strpos($folderName, "/") !== false) {
                array_push($warningSearchWillFail_Arr, $folderName);
            }
        }
        
        // Filter files based on extensions or if dir
        // $filteredFiles = [];
        // foreach ($files as $file) {
        //     if (is_dir($file) || (in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json']) && !preg_match('/\.no\.(md|json)$/', $file))) {
        //     // if (is_dir($file) || in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json'])) {
        //         array_push($filteredFiles, $file);
        //     }
        // }

        $filteredFiles = []; // Initialize an empty array to store filtered files

        // foreach ($files as $file) {
        //     // Check if the file is a directory
        //     $isDirectory = is_dir($file);

        //     // Get the file extension
        //     $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        //     // Check if the file has a valid extension (md or json)
        //     // $isValidExtension = in_array($fileExtension, ['md', 'json']);
        //     $isValidExtension = in_array($fileExtension, ['md']);

        //     // Check if the file name does not end with '.no.md' or '.no.json'
        //     $isNotExcluded = !preg_match('/\.no\.md$/', $file) && !preg_match('/\.hide\.md$/', $file);

        //     // If the file is not a directory and has a valid, non-excluded extension, add it to the filtered list
        //     if ($isDirectory && $isValidExtension && $isNotExcluded) {
        //         $filteredFiles[] = $file;
        //     }
        // }
        // $filteredFiles = [];
        // foreach ($files as $file) {
        //     if (is_dir($file) || (in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json']) && !preg_match('/\.no\.(md|json)$/', $file))) {
        //     // if (is_dir($file) || in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json'])) {
        //         $filteredFiles[] = $file;
        //     }
        // }
        $filteredFiles = [];
        foreach ($files as $file) {
          // if directory, is acceptable file extension, though no secondary extension .hide or .no
            if (is_dir($file) || (in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json']) && !preg_match('/\.(no|hide)\.md$/', $file))) {
            // if (is_dir($file) || in_array(pathinfo($file, PATHINFO_EXTENSION), ['md', 'json'])) {
                $filteredFiles[] = $file;
            }
        }

        // $filteredFiles = [];
        // foreach ($files as $file) {
        //     // Check if the file is a directory or if the filename starts with a dot
        //     if (is_dir($file) || $file[0] === '.') {
        //         continue; // Skip the file if it's a directory or starts with a dot
        //     }

        //     // Get the file extension
        //     $extension = pathinfo($file, PATHINFO_EXTENSION);

        //     // Check if the file ends with .no.md or does not end with .md
        //     // Don't hide sortspec.md or else the JS can't detect the file's content and perform custom sort
        //     // if ( $extension === 'md' && !preg_match('/\.no\.md$/', $file) && !preg_match('/sortspec\.md$/', $file) ) {
        //     if ( $extension === 'md' && !preg_match('/\.no\.md$/', $file)  ) {
        //         array_push($filteredFiles, $file);
        //     }
        // } // foreach
        
        return $filteredFiles;
    } // rglob

      $dirs = rglob("$DIR_SNIPPETS?*");
      $lookup_metas = [];

      function map_tp_dec($path) { // trailing parsed (removed preceding snippet/ and may remove ending slash /) and decorated object
        // var_dump($path);
        global $DIR_SNIPPETS;
        global $DEFAULT_THUMBNAIL_SIZE;
        global $lookup_metas;
        
        // tp trailing path?
        $path_tp = substr($path, strlen($DIR_SNIPPETS)); // trailing parsed

        // Assure trailing forward slash /
        // $lastChar = $path[strlen($path)-1];
        // $path = ($lastChar==='/') ? $path : "$path/";
        // $desc = $thumbnail = $gotos = null;
        

        $decorated = [
          "current" => "",
          "path" => $path,
          "path_tp" => $path_tp,
          "next" => []
        ];
        //var_dump($path);
        //echo "<br/>";

        $lastFiveChars = substr($path, -5); // Changed to last 6 chars in case of a .no.md that you want to ignore in the learning app


        if (stripos($lastFiveChars, ".json") !== false) { // Btw, .no.json files had already been stripped away
          $lookup_metas[$path] = @json_decode(file_get_contents($path), true);
          //var_dump( $lookup_metas[$path]);
          //die();
        }

        // var_dump($lookup_metas);
        // die();
        if (stripos($lastFiveChars, ".md") !== false) { // Btw, .no.md files had already been stripped away
          // var_dump($lookup_metas);
          // die();
          if(!isset($lookup_metas[$path]["summary"]))
            $lookup_metas[$path]["summary"] = array();
          $file_contents = "";
          $file_contents = @file_get_contents($path);
          array_push($lookup_metas[$path]["summary"], $file_contents);
          // var_dump($lookup_metas);
          // die();
          // $lookup_metas[$path]["summary"] .= file_get_contents($path . "+meta.txt");
        }

        // die();
        
        return $decorated;
      } // map_tp_dec
      $dirs = array_map("map_tp_dec", $dirs);

      echo "<script>";
      echo "var folders = " . json_encode($dirs) . ",";
      echo "ori = folders, ";
      echo "lookupMetas = " . json_encode($lookup_metas) . ";";
      echo "</script>";

      // var_dump($lookup_metas);
      // die();

      // var_dump($dirs);
      // die();
    ?>

    <script>
    // Sort the folders array based on the order defined in sortCriteria
    // Please note this only work on remote because the remote copy will switch out the path to some Obsidian path in another ~ folder, 
    // whereas remote copy will have Obsidian path in the same root folder
    if(lookupMetas?.["curriculum/sortspec.md"]?.summary?.[0]) {
      // This is the content of sortspec.md
      // const sortCriteriaMd = `
      // ---
      // sorting-spec: |
      //   AI App Development
      //   Game Development, Unreal
      //   Web Development
      //   Web Development - Rapid Development
      // ---
      // `;
      const sortCriteriaMd = lookupMetas?.["curriculum/sortspec.md"]?.summary?.[0];
      console.log({sortCriteriaMd})

      // Function to parse the sorting spec
      function parseSortSpec(content) {
        // Find the sorting-spec block and extract the folders
        const match = content.match(/sorting-spec:\s*\|\s*([\s\S]*?)\s*---/);
        if (match && match[1]) {
          // Split the block into lines and trim whitespace
          return match[1].split('\n').map(s => s.trim()).filter(Boolean);
        }
        return [];
      }

      // Get the ordered folders from the sort spec

      const sortCriteria = parseSortSpec(sortCriteriaMd);
      console.log({sortCriteria}); // Logs the ordered folder names criteria

      // Sort the folders array based on the order defined in sortCriteria
      folders = folders.sort((a, b) => {
          const indexA = sortCriteria.indexOf(a.path_tp);
          const indexB = sortCriteria.indexOf(b.path_tp);
          if (indexA !== -1 && indexB !== -1) {
            return indexA - indexB; // both in ordered list, sort by their order
          } else if (indexA !== -1) {
            return -1; // only a is in ordered list, a comes first
          } else if (indexB !== -1) {
            return 1; // only b is in ordered list, b comes first
          } else {
            return a.path_tp.localeCompare(b.path_tp); // neither in ordered list, sort alphabetically
          }
        });


      console.log("Retrieved sortspec.md from Obsidian and rearranged folders: " + folders.map(f=>f.path_tp))

    } // if there's a sortspec.md
    </script>


<style>
#searcher {
  width:180px;
}
#searcher-2 {
  width:180px;
}

@media screen AND (min-width: 550px) {
  #searcher-btn::after, #searcher-2-btn::after {
    content: " Search";
  }
  #searcher-container, #searcher-container-2 {
    width:370px;
    text-align: right;
  }

}
@media screen AND (max-width: 549px) {
  #searcher-containers {
    width: 100%;
  }
  #searcher-container, #searcher-container-2 {
    width:100% !important;
  }
  #searcher-containers label {
    display:block !important;
    text-align: center !important;
    width: 100%;
  }
  #searcher {
    width:80%;
  }
  #searcher-2 {
    width:80%;
  }
  #searcher + button, #searcher-2 + button {
    width: 15%;
  }
}
</style>

    <script>
    <?php
        echo 'var realpath = "' . dirname(realpath("explorer.php")) . '"';
    ?>
    </script>

    <script src="assets/js/explorer.js"></script>
    <script src="assets/js/multistates.js"></script>

</head>
    <body>
        <div class="container">
        
          <?php

            if(!`which $pcregrep 2>/dev/null`) {
              echo "<div class='error'>Error: Your server does not support pcregrep necessary to find text in files. Search will fail. Please contact your server administrator.</div>";
            }

            if(count($warningSearchWillFail_Arr)>0) {
              echo "<div class='error'>Error: A folder has illegal characters : or /. Search will produce inaccurate results when hitting such folder(s). Please contact your server administrator to rename these folders:
              <ul>";
              foreach($warningSearchWillFail_Arr as $illegalFolder) {
                echo "<li>$illegalFolder</li>";
              }
              echo "</ul></div>";
            }

          ?>

          <div style="width:1px; height:10px; clear:both;"></div>
          <div id="searcher-containers" style="float:right; padding:15px;">

            <div id="searcher-container" style="float:right; margin-top:5px;">
                  <!-- <label for="searcher">Text content:</label>
                  <input id="searcher" onkeyup="checkSearcherSubmit(event, $('#searcher-btn'))" class="toolbar" type="text" placeholder="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                  <button id="searcher-btn" class="override-ios-button-style" onclick="doSearcher();" style="cursor: pointer;"><span class="fa fa-search" style="cursor: pointer;"></span></button> -->
                  <span class="mobile-flush-top">
                    <button onclick="if(confirm('Clear Find text field?')) clearSearcher();" style="cursor: pointer; border:0;"><span class="fa fa-eraser" style="cursor: pointer;"> Clear</button>
                    <button onclick="toggleAllExpand();" style="cursor: pointer; border:0;"><span class="fa fa-eye" style="cursor: pointer;"> Toggle All</button>
                    <button onclick="window.print();" style="cursor: pointer; border:0;"><span class="fa fa-print" style="cursor: pointer;"> Print</button>
                  </span>
            </div>

            
            <div id="searcher-container-2" style="float:right; margin-top:5px;">
                  <label for="searcher-2">Topic Title:</label>
                  <input id="searcher-2" onkeyup="checkSearcherSubmit(event, $('#searcher-2-btn'))"class="toolbar" type="text" placeholder="" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                  <button id="searcher-2-btn" class="override-ios-button-style" 
                    onclick="doSearcher2($('#searcher-2').val(), ()=>{ 
                      $('#shareSnippet').val((window.location.hostname + window.location.pathname).replaceAll('explorer.php', '') + `?search-titles=${encodeURI($('#searcher-2').val())}`);
                      document.getElementById('share-search-title-wrapper').classList.remove('hidden')
                    }); " 
                    style="cursor: pointer;"
                  ><span class="fa fa-search" style="cursor: pointer;"></span></button>
            </div>
            <div style="clear:both;"></div>
            <div id="share-search-title-wrapper" class="hidden" 
              style="margin-top:10px; text-align:right; position:fixed; right:10px; bottom:0; padding:0; background-color:white;">
              <a href="javascript:void()" onclick='$("#shareModal").modal("show");'>
                <i>Share the search:</i>
                <span class="fas fa-share-alt"></span>
              </a>
            </div>
          </div> <!-- #searcher-containers -->


          <script>
          $(document).ready(function() {
            $('#copyButton').click(function() {
              var copyText = document.getElementById("shareSnippet");
              copyText.select();
              document.execCommand("copy");
              // alert("Copied the text: " + copyText.value); // Optional: alert message
            });
          });

          $(window).scroll(function() {
            // if($(window).scrollTop() + $(window).height() == $(document).height()) {
              // alert("bottom!");
              // window.print();
            // }
            $("#share-search-title-wrapper").addClass("hidden");
          });
          </script>

          
          <!-- Modal -->
          <div class="modal fade" id="shareModal" tabindex="-1" role="dialog" aria-labelledby="shareModalLabel">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="shareModalLabel">Share this link</h4>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position:absolute; top:10px; right:10px;">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <!-- Embed Code Textarea -->
                  <textarea id="shareSnippet" class="form-control" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                  <!-- Copy to Clipboard Button -->
                  <button type="button" class="btn btn-default" id="copyButton">
                    <i class="fas fa-copy"></i> Copy to Clipboard
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div id="printer-title"></div>
          <br style="clear:both;"/><br/>

          <main id="target">
          </main>

          <br/>
          <div id="search-results" class="deemp-fieldset" style="display:none;">
            <legend style="font-size:15.75px;"><span class="fa fa-search"></span> Search Results</legend>
            <div class="contents"></div>
          </div>

        </div> <!-- /.container -->

        <div id="copied-message" style="display:none; position:fixed; border-radius:5px; top:0; right:0; color:green; background-color:rgba(255,255,255,1); padding: 5px 10px 5px 5px;">Copied!</div>
        
        <style id="style-toggle-all-expand">
        </style>

        <!-- Highlighter -->
        <script src="assets/js/vendors/jquery.highlight.js"></script>

        <!-- Designer: Open Sans, Lato, FontAwesome, Waypoints, Skrollr, Pixel-Em-Converter -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300|Open+Sans+Condensed:300" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.0/jquery.waypoints.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/skrollr/0.6.30/skrollr.min.js"></script>
        <script src="https://raw.githack.com/filamentgroup/jQuery-Pixel-Em-Converter/master/pxem.jQuery.js"></script>
        
        <!-- Rendering: Handlebars JS, LiveQuery, Sprintf JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.js"></script>
        <script src="assets/js/vendors/livequery.js"></script>
        <script src="https://raw.githack.com/azatoth/jquery-sprintf/master/jquery.sprintf.js"></script>
        
        <!-- Compatibility: Modernizr, jQuery Migrate (check browser) -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
        <script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        
        <!-- Mobile: jQuery UI, jQuery UI Touch Punch -->
        <link href="https://code.jquery.com/ui/1.11.3/themes/ui-lightness/jquery-ui.css" rel="stylesheet"/>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>
       
        <!-- Bootstrap JS -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
        
        <!-- Friendlier API: ListHandlers, Timeout -->
        <script src="https://raw.githack.com/Inducido/jquery-handler-toolkit.js/master/jquery-handler-toolkit.js"></script>
        <script src="https://raw.githack.com/tkem/jquery-timeout/master/src/jquery.timeout.js"></script>
        <!-- Autosize textarea
            https://gomakethings.com/automatically-expand-a-textarea-as-the-user-types-using-vanilla-javascript/
        -->
        <script src="assets/js/vendors/autoExpand/autoExpand.js"></script>

        <!-- Highlighter -->
        <script src="assets/js/vendors/jquery.highlight.js"></script>
        
    </body>
</html>