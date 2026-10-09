<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $library_item->title }}</title>
    <style>
        html, body {
            background-color: #2f3136;
            margin: 0;
            padding: 0;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        #reader_topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 48px;
            z-index: 30;
            background: #1f2023;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 600;
            gap: 10px;
        }

        #reader_topbar .secure-badge {
            margin-left: auto;
            font-size: 11px;
            font-weight: 500;
            background: rgba(255, 255, 255, .12);
            padding: 4px 10px;
            border-radius: 20px;
        }

        #my_pdf_viewer {
            margin-top: 48px;
            margin-bottom: 50px;
        }

        #canvas_container {
            width: 100%;
            height: calc(100vh - 98px);
            overflow: auto;
            background: #2f3136;
            text-align: center;
        }

        #canvas_container canvas {
            box-shadow: 0 0 12px rgba(0, 0, 0, .45);
        }

        #navigation_controls {
            background-color: #4b9d8a;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            display: flex;
            z-index: 30;
        }

        #go_previous,
        #go_next {
            width: 50%;
            max-width: 200px;
            height: 100%;
            background-color: #ffffff;
            border: none;
            cursor: pointer;
            font-weight: 700;
        }

        #current_page {
            height: 48px;
            width: 100%;
            border: none;
            background-color: #ffcd99;
            text-align: center;
            font-weight: 900;
            font-size: 18px;
        }

        #zoom_controls {
            background-color: #0000;
            position: fixed;
            bottom: 55px;
            right: 0;
            left: 0;
            text-align: center;
            z-index: 29;
        }

        #zoom_controls button {
            width: 40px;
            height: 40px;
            font-size: 20px;
            font-weight: 700;
            border: none;
            border-radius: 50%;
            margin: 0 4px;
            background: rgba(255, 255, 255, .92);
            cursor: pointer;
        }

        #wm_layer {
            position: fixed;
            inset: 0;
            z-index: 25;
            pointer-events: none;
            overflow: hidden;
        }

        #wm_layer span {
            position: absolute;
            transform: rotate(-28deg);
            color: rgba(255, 255, 255, 0.10);
            font-size: 15px;
            font-weight: 700;
            white-space: nowrap;
            pointer-events: none;
        }
    </style>
    <script src="{{ asset('assets/global/pdf-canvas/pdf.min.js') }}"></script>
    <script src="{{ asset('assets/global/pdf-canvas/pdf.worker.min.js') }}"></script>
</head>

<body>
    <div id="reader_topbar">
        <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $library_item->title }}</span>
        <span class="secure-badge">🔒 {{ get_phrase('Protected') }}</span>
    </div>

    <div id="wm_layer"></div>

    <div id="my_pdf_viewer">
        <div id="canvas_container">
            <canvas id="pdf_renderer"></canvas>
        </div>

        <div id="navigation_controls">
            <button id="go_previous">{{ get_phrase('Previous') }}</button>
            <input id="current_page" value="1" type="number" min="1" />
            <button id="go_next">{{ get_phrase('Next') }}</button>
        </div>

        <div id="zoom_controls">
            <button id="zoom_in">+</button>
            <button id="zoom_out">-</button>
        </div>
    </div>

    <script>
        "use strict";
        var myState = {
            pdf: null,
            currentPage: 1,
            zoom: 1
        };

        // ---- watermark (traceable) --------------------------------------------
        (function buildWatermark() {
            var text = "{{ addslashes(auth()->user()->name) }} · {{ auth()->user()->id }}";
            var layer = document.getElementById('wm_layer');
            var stepX = 320,
                stepY = 170;
            for (let y = 40; y < window.innerHeight + 200; y += stepY) {
                for (let x = -40; x < window.innerWidth + 120; x += stepX) {
                    var s = document.createElement('span');
                    s.textContent = text;
                    s.style.top = y + 'px';
                    s.style.left = x + 'px';
                    s.style.display = 'block';
                    s.style.transform = 'rotate(-28deg)';
                    wm_layer.appendChild(s);
                }
            }
        })();

        pdfjsLib.getDocument("{{ route('library.stream', $library_item->id) }}").then((pdf) => {
            myState.pdf = pdf;
            render();
        });

        function render() {
            myState.pdf.getPage(myState.currentPage).then((page) => {
                var canvas = document.getElementById("pdf_renderer");
                var ctx = canvas.getContext('2d');
                var viewport = page.getViewport(myState.zoom);
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                page.render({
                    canvasContext: ctx,
                    viewport: viewport
                });
            });
        }

        document.getElementById('go_previous').addEventListener('click', () => {
            if (myState.pdf == null || myState.currentPage == 1) return;
            myState.currentPage -= 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
        });

        document.getElementById('go_next').addEventListener('click', () => {
            if (myState.pdf == null || myState.currentPage > myState.pdf._pdfInfo.numPages) return;
            myState.currentPage += 1;
            document.getElementById("current_page").value = myState.currentPage;
            render();
        });

        document.getElementById('current_page').addEventListener('keypress', (e) => {
            if (myState.pdf == null) return;
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                var desiredPage = document.getElementById('current_page').valueAsNumber;
                if (desiredPage >= 1 && desiredPage <= myState.pdf._pdfInfo.numPages) {
                    myState.currentPage = desiredPage;
                    render();
                }
            }
        });

        document.getElementById('zoom_in').addEventListener('click', () => {
            if (myState.pdf == null) return;
            myState.zoom += 0.2;
            render();
        });

        document.getElementById('zoom_out').addEventListener('click', () => {
            if (myState.pdf == null) return;
            if (myState.zoom <= 0.4) return;
            myState.zoom -= 0.2;
            render();
        });

        // Block right-click / save / print / copy shortcuts
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('keydown', function(e) {
            var k = (e.key || '').toLowerCase();
            if ((e.ctrlKey || e.metaKey) && ['s', 'p', 'c', 'u', 'a'].includes(k)) {
                e.preventDefault();
                return false;
            }
            if (k === 'f12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c'].includes(k))) {
                e.preventDefault();
                return false;
            }
        });
        document.addEventListener('dragstart', e => e.preventDefault());
    </script>
</body>

</html>
