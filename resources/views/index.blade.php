<html>
    <head>
        <!-- Styles -->
        <link 
            rel="stylesheet" 
            type="text/css" 
            href="{{ asset('css/style.css') }}">

        <link 
            rel="stylesheet" 
            type="text/css" 
            href="{{ asset('css/progress.css') }}">

        <!-- Favicon stuff -->
        <link 
            rel="apple-touch-icon" 
            sizes="180x180" 
            href="{{ asset('image/apple-touch-icon.png') }}">

        <link 
            rel="icon" 
            type="image/png" 
            sizes="32x32" 
            href="{{ asset('image/favicon-32x32.png') }}">

        <link 
            rel="icon" 
            type="image/png" 
            sizes="16x16" 
            href="{{ asset('image/favicon-16x16.png') }}">

        <link 
            rel="manifest" 
            href="{{ asset('image/site.webmanifest') }}">

        <link 
            rel="mask-icon" 
            href="{{ asset('image/safari-pinned-tab.svg') }}" 
            color="#5bbad5">

        <meta 
            name="msapplication-TileColor" 
            content="#da532c">

        <meta 
            name="theme-color" 
            content="#ffffff">
    </head>

    <body>
        <div id="border">
            <div id="new" class="panel">
                <h1>New Game</h1> 

                <p class="panel-text">Select field size</p>
                
                <select id="new-select" class="panel-select">
                  <option value="value1" selected="selected">15x11</option>
                  <option value="value2">25x15</option>
                  <option value="value3">35x25</option>
                </select>
                
                <button id="new-button" class="panel-button">Next</button>        
            </div>


            <div id="game">
                <canvas id="game-canvas"></canvas>
                
                <!-- Buttons -->
                <div id="game-buttons" class="game-buttons">
                </div>

                <!-- Current player's turn -->
                <div id="game-players-state" class="panel-text"></div>

                <!-- Players progress -->
                <br>
                <div id="progress-bar-1" class="progress-bar">
                    <div id="progress-1" class="progress-bar-value"></div>
                    <div id="progress-circle-1" class="progress-circle">P1</div>
                </div>
                <br>
                <div id="progress-bar-2" class="progress-bar">
                    <div id="progress-2" class="progress-bar-value"></div>
                    <div id="progress-circle-2" class="progress-circle">P2</div>
                </div>
            </div>

            <div id="scripts">
                <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
                <script type="module" src="{{ asset('js/app.js') }}"></script>
            </div>
        </div>
    </body>
</html>
