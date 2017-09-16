window.addEventListener('load', function() {
    window.searchDropdown = document.getElementById('radio_dropdown_wrapper');
    window.searchDownArrow = searchDropdown.firstChild;
    window.searchMenu = searchDownArrow.nextSibling;
    
    window.shown = false;

    searchDownArrow.addEventListener('click', function(e) {
        if (!shown) {
            searchMenu.style.display = 'block';
        } else {
            searchMenu.style.display = 'none';
        }
        shown = !shown;
        
        e.stopPropagation();
    });
    
    document.body.addEventListener('click', function() {
        if (shown) {
            searchMenu.style.display = 'none';
            shown = false;
        }
    });
    
    searchMenu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    var radios = searchMenu.getElementsByTagName('input'), i;
    for (i = 0; i < radios.length; i++) {
        radios[i].addEventListener('click', function(e) {
            setTimeout(function(){
                searchMenu.style.display = 'none';
                shown = false;
            }, 250);
            
            searchDropdown.previousSibling.placeholder = 'Search ' + this.value + '...';
        });
    }
});
