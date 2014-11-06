function Gzoom (url) {
GzoomWindow = window.open(url, "PNPerf", "width=<?php echo $graph_width ?>,height=<?php echo $graph_height ?>,location=no,status=no,resizable=yes,scrollbars=yes");
GzoomWindow.focus();
}
