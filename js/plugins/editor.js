window.addEventListener('load', function() {
    var editor;
});
editor = ContentTools.EditorApp.get();
editor.init('*[data-editable]', 'data-name');
editor.bind('save', function (regions) {
    var name, onStateChange, payload, xhr;
    // Set the editor as busy while we save our changes
    this.busy(true);

    // Collect the contents of each region into a FormData instance
    payload = new FormData();
    for (name in regions) {
        payload.append(name, regions[name]);
    }
        
    // Send the update content to the server to be saved
    onStateChange = function(ev) {
        // Check if the request is finished
        if (ev.target.readyState == 4) {
            editor.busy(false);
            if (ev.target.status == '200') {
                // Save was successful, notify the user with a flash
                new ContentTools.FlashUI('ok');
            } else {
                // Save failed, notify the user with a flash
                new ContentTools.FlashUI('no');
            }
        }
    };
    xhr = new XMLHttpRequest();
    xhr.addEventListener('readystatechange', onStateChange);
    xhr.open('POST', 'index.php?plugin=pages&amp;op=save');
    xhr.send(payload);
}