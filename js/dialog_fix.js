// Bind the ajaxComplete event to the document
$(document).ajaxComplete(function(event, xhr, settings) {
  // Check if it's the specific AJAX request you're interested in
  if (settings.url === '/index.php/apps/files_sharing/api/externalShares') {    
    // Create a MutationObserver instance
    var observer = new MutationObserver(function(mutationsList) {
      for (var mutation of mutationsList) {
        if (mutation.type === 'childList') {
          // Check if the added nodes contain the element you want to modify
          var addedNodes = mutation.addedNodes;
          for (var i = 0; i < addedNodes.length; i++) {
            var addedNode = addedNodes[i];
            if ($(addedNode).attr('class')){
              if (addedNode.classList.contains('oc-dialog')) {
                $(addedNode).find('button').each(function(i,e){
                  
                  var ele = e;
                  console.log(ele);
                  setTimeout(() => {
                    if($(ele).text() == 'Add remote share') {
                      $(ele).hide();
                        $(ele).text('No');
                        $(ele).show();
                        console.log($(ele).text());
                    }
                  }, 100);
                })
              }
            }
          }
        }
      }
    });

    // Start observing the document body for changes
    observer.observe(document.body, { childList: true, subtree: true });
  }
});