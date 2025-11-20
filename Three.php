<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Window Generator - Three.js Demo</title>
    <style>
        body { 
            margin: 0; 
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .controls {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            max-width: 960px;
        }
        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .form-group {
            margin-bottom: 12px;
            min-width: 160px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }
        input[type="number"], select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 2px solid #e1e5e9;
            box-sizing: border-box;
        }
        input[type="number"]:focus, select:focus {
            outline: none;
            border-color: #007bff;
        }
        button {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        #container {
            width: 100%;
            height: 600px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .info {
            margin-top: 15px;
            color: #666;
            font-size: 12px;
            padding: 10px;
            background: rgba(0,123,255,0.1);
            border-radius: 6px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .window-state {
            margin-top: 10px;
            padding: 8px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 4px;
            border-left: 4px solid #28a745;
        }
        small {
            color: #666;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="controls">
        <h1>🏠 3D Window Generator (Improved Hinges)</h1>
        <p>Enter dimensions (cm) and component widths (cm). Click the window to open/close the sash.</p>
        
        <form method="POST" id="paramsForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="width">Width (cm)</label>
                    <input type="number" id="width" name="width" value="<?php echo isset($_POST['width']) ? htmlspecialchars($_POST['width']) : '100'; ?>" min="10" max="500" step="1" required>
                </div>
                
                <div class="form-group">
                    <label for="height">Height (cm)</label>
                    <input type="number" id="height" name="height" value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : '150'; ?>" min="10" max="500" step="1" required>
                </div>
                
                <div class="form-group">
                    <label for="thickness">Wall thickness (cm)</label>
                    <input type="number" id="thickness" name="thickness" value="<?php echo isset($_POST['thickness']) ? htmlspecialchars($_POST['thickness']) : '5'; ?>" min="1" max="50" step="0.5" required>
                </div>
                
                <div class="form-group">
                    <label for="frameWidth">Frame member width (cm)</label>
                    <input type="number" id="frameWidth" name="frameWidth" value="<?php echo isset($_POST['frameWidth']) ? htmlspecialchars($_POST['frameWidth']) : '5'; ?>" min="0.5" max="50" step="0.5">
                </div>
                
                <div class="form-group">
                    <label for="sashWidth">Sash member width (cm)</label>
                    <input type="number" id="sashWidth" name="sashWidth" value="<?php echo isset($_POST['sashWidth']) ? htmlspecialchars($_POST['sashWidth']) : '4'; ?>" min="0.5" max="50" step="0.5">
                </div>
                
                <div class="form-group">
                    <label for="hingeCount">Hinge Count</label>
                    <input type="number" id="hingeCount" name="hingeCount" value="<?php echo isset($_POST['hingeCount']) ? htmlspecialchars($_POST['hingeCount']) : '2'; ?>" min="1" max="4" step="1">
                </div>
                
                <div class="form-group">
                    <label for="hingeSide">Hinge Side</label>
                    <select id="hingeSide" name="hingeSide">
                        <option value="left" <?php echo (isset($_POST['hingeSide']) && $_POST['hingeSide'] == 'left') ? 'selected' : ''; ?>>Left Hinges</option>
                        <option value="right" <?php echo (isset($_POST['hingeSide']) && $_POST['hingeSide'] == 'right') ? 'selected' : ''; ?>>Right Hinges</option>
                    </select>
                </div>
            </div>

            <!-- Optional: individual parts -->
            <div style="margin-top:8px;">
                <small>(Optional) tweak individual part widths, defaults derived from frame/sash values</small>
            </div>

            <div class="form-row" style="margin-top:8px;">
                <div class="form-group">
                    <label for="jambWidth">Left/Right jamb width (cm)</label>
                    <input type="number" id="jambWidth" name="jambWidth" value="<?php echo isset($_POST['jambWidth']) ? htmlspecialchars($_POST['jambWidth']) : ''; ?>" placeholder="leave empty to use frameWidth">
                </div>
                
                <div class="form-group">
                    <label for="stileWidth">Left/Right stile width (cm)</label>
                    <input type="number" id="stileWidth" name="stileWidth" value="<?php echo isset($_POST['stileWidth']) ? htmlspecialchars($_POST['stileWidth']) : ''; ?>" placeholder="leave empty to use sashWidth">
                </div>
                
                <div class="form-group">
                    <label for="railWidth">Top/Bottom rail width (cm)</label>
                    <input type="number" id="railWidth" name="railWidth" value="<?php echo isset($_POST['railWidth']) ? htmlspecialchars($_POST['railWidth']) : ''; ?>" placeholder="leave empty to use sashWidth">
                </div>
                
                <div class="form-group">
                    <label for="cillWidth">Frame cill thickness (cm)</label>
                    <input type="number" id="cillWidth" name="cillWidth" value="<?php echo isset($_POST['cillWidth']) ? htmlspecialchars($_POST['cillWidth']) : ''; ?>" placeholder="optional">
                </div>
            </div>

            <div style="margin-top:12px;">
                <button type="submit">Generate 3D Window</button>
            </div>
        </form>
        
        <div class="info">
            <p>🎮 Controls: Left click + drag to rotate • Right click + drag to pan • Scroll to zoom</p>
            <p>🪟 <strong>Click the window to open/close it!</strong> (Only the sash moves - realistic casement window)</p>
            <div class="window-state" id="windowState">Window State: Closed</div>
            <p style="color: #5bc0de;">✨ Perfect pivot point and hinge placement</p>
        </div>
    </div>

    <div id="container"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.min.js"></script>

    <script>
        // Get PHP values or use defaults
        const width = <?php echo isset($_POST['width']) ? floatval($_POST['width']) : 100; ?>;
        const height = <?php echo isset($_POST['height']) ? floatval($_POST['height']) : 150; ?>;
        const thickness = <?php echo isset($_POST['thickness']) ? floatval($_POST['thickness']) : 5; ?>;
        const frameWidth = <?php echo isset($_POST['frameWidth']) ? floatval($_POST['frameWidth']) : 5; ?>;
        const sashWidth = <?php echo isset($_POST['sashWidth']) ? floatval($_POST['sashWidth']) : 4; ?>;
        const hingeCount = <?php echo isset($_POST['hingeCount']) ? intval($_POST['hingeCount']) : 2; ?>;
        const hingeSide = "<?php echo isset($_POST['hingeSide']) ? htmlspecialchars($_POST['hingeSide']) : 'left'; ?>";
        const jambWidth = <?php echo isset($_POST['jambWidth']) && $_POST['jambWidth'] !== '' ? floatval($_POST['jambWidth']) : 'null'; ?>;
        const stileWidth = <?php echo isset($_POST['stileWidth']) && $_POST['stileWidth'] !== '' ? floatval($_POST['stileWidth']) : 'null'; ?>;
        const railWidth = <?php echo isset($_POST['railWidth']) && $_POST['railWidth'] !== '' ? floatval($_POST['railWidth']) : 'null'; ?>;
        const cillWidth = <?php echo isset($_POST['cillWidth']) && $_POST['cillWidth'] !== '' ? floatval($_POST['cillWidth']) : 'null'; ?>;

        // Scale factor for better visualization (convert cm to Three.js units)
        const scale = 0.01;

        // Scene setup
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x87CEEB); // Sky blue background

        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(document.getElementById('container').clientWidth, document.getElementById('container').clientHeight);
        document.getElementById('container').appendChild(renderer.domElement);

        // Add orbit controls
        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;

        // Enhanced Lighting - Position lights for better front view
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(0, 5, 5); // Front-top lighting
        scene.add(directionalLight);

        // Window state management
        let windowSash = null;
        let isWindowOpen = false;
        let windowRotation = 0;
        const windowStateElement = document.getElementById('windowState');

        // Fixed wall creation with thicker walls
        function createWallWithSmartSizing(windowWidth, windowHeight, frameThickness) {
            const wallGroup = new THREE.Group();
            
            const w = windowWidth * scale;
            const h = windowHeight * scale;
            const t = frameThickness * scale;
            
            const baseWallWidth = 400 * scale;
            const wallDepth = 15 * scale;
            
            const stoneMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x7a7a7a,
                shininess: 10
            });

            const minWallHeight = 300 * scale;
            const windowBottomFromFloor = 80 * scale;
            
            const requiredWallHeight = Math.max(
                minWallHeight,
                windowBottomFromFloor + h + 50 * scale
            );
            
            const wallHeight = requiredWallHeight;
            const wallBottom = -wallHeight / 2;
            const wallTop = wallHeight / 2;

            const windowBottom = wallBottom + windowBottomFromFloor;
            const windowTop = windowBottom + h;
            const windowCenterY = windowBottom + h/2;

            const minWallThickness = 50 * scale;
            const sideWallWidth = Math.max((baseWallWidth - w) / 2, minWallThickness);
            const actualWallWidth = w + (sideWallWidth * 2);
            const topWallHeight = Math.max(wallTop - windowTop, minWallThickness);
            const bottomWallHeight = Math.max(windowBottom - wallBottom, minWallThickness);

            // Create the 4 wall pieces
            if (sideWallWidth > 0) {
                const leftWall = new THREE.Mesh(new THREE.BoxGeometry(sideWallWidth, wallHeight, wallDepth), stoneMaterial);
                leftWall.position.x = -(w + sideWallWidth) / 2;
                leftWall.position.y = 0;
                wallGroup.add(leftWall);

                const rightWall = new THREE.Mesh(new THREE.BoxGeometry(sideWallWidth, wallHeight, wallDepth), stoneMaterial);
                rightWall.position.x = (w + sideWallWidth) / 2;
                rightWall.position.y = 0;
                wallGroup.add(rightWall);
            }
            
            if (topWallHeight > 0) {
                const topWall = new THREE.Mesh(new THREE.BoxGeometry(w, topWallHeight, wallDepth), stoneMaterial);
                topWall.position.x = 0;
                topWall.position.y = windowTop + topWallHeight/2;
                wallGroup.add(topWall);
            }
            
            if (bottomWallHeight > 0) {
                const bottomWall = new THREE.Mesh(new THREE.BoxGeometry(w, bottomWallHeight, wallDepth), stoneMaterial);
                bottomWall.position.x = 0;
                bottomWall.position.y = wallBottom + bottomWallHeight/2;
                wallGroup.add(bottomWall);
            }

            return { 
                wallGroup, 
                windowPositionY: windowCenterY, 
                wallWidth: actualWallWidth,
                wallHeight, 
                wallDepth,
                wallBottom,
                wallTop
            };
        }

        // Create wall
        const { wallGroup, windowPositionY, wallWidth, wallHeight, wallDepth, wallBottom, wallTop } = createWallWithSmartSizing(width, height, thickness);
        scene.add(wallGroup);

        // Create detailed casement window with ALL CONFIGURABLE PARAMETERS
        function createDetailedCasementWindow(params) {
            const { 
                widthCm, heightCm, frameThicknessCm, frameWidthCm, sashWidthCm, 
                jambWidthCm, stileWidthCm, railWidthCm, cillWidthCm,
                hingeCount, hingeSide 
            } = params;

            const windowGroup = new THREE.Group();
            
            const w = widthCm * scale;
            const h = heightCm * scale;
            const t = frameThicknessCm * scale;
            
            // Use provided values or defaults
            const frameW = frameWidthCm * scale;
            const sashW = sashWidthCm * scale;
            const jambW = (jambWidthCm !== null) ? jambWidthCm * scale : frameW;
            const stileW = (stileWidthCm !== null) ? stileWidthCm * scale : sashW;
            const railW = (railWidthCm !== null) ? railWidthCm * scale : sashW;
            const cillW = (cillWidthCm !== null) ? cillWidthCm * scale : (frameW * 0.8);

            // Frame material
            const frameMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x8B4513,
                shininess: 30
            });
            
            // Sash material
            const sashMaterial = new THREE.MeshPhongMaterial({ 
                color: 0xDEB887,
                shininess: 40
            });
            
            // Hardware material
            const hardwareMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x696969,
                shininess: 80
            });

            const glassMaterial = new THREE.MeshPhongMaterial({ 
                color: 0xa0c8e0,
                transparent: true,
                opacity: 0.3,
                shininess: 100
            });

            // DIMENSIONS
            const zOffset = 0.1 * scale;
            const frameDepth = t * 1.5;
            const sashDepth = t;
            const sashClearance = 0.2 * scale;

            // 1. MAIN FRAME (fixed to wall)
            const mainFrameGroup = new THREE.Group();
            
            // HEAD (top frame)
            const head = new THREE.Mesh(new THREE.BoxGeometry(w, frameW, frameDepth), frameMaterial);
            head.position.set(0, h/2 - frameW/2, frameDepth/2 + zOffset);
            mainFrameGroup.add(head);

            // CILL (bottom frame)
            const cill = new THREE.Mesh(new THREE.BoxGeometry(w, cillW, frameDepth), frameMaterial);
            cill.position.set(0, -h/2 + cillW/2, frameDepth/2 + zOffset);
            mainFrameGroup.add(cill);

            // Left and right jambs
            const leftJamb = new THREE.Mesh(new THREE.BoxGeometry(jambW, h - frameW - cillW, frameDepth), frameMaterial);
            leftJamb.position.set(-w/2 + jambW/2, (-h/2 + cillW) + (h - frameW - cillW)/2, frameDepth/2 + zOffset);
            mainFrameGroup.add(leftJamb);

            const rightJamb = new THREE.Mesh(new THREE.BoxGeometry(jambW, h - frameW - cillW, frameDepth), frameMaterial);
            rightJamb.position.set(w/2 - jambW/2, (-h/2 + cillW) + (h - frameW - cillW)/2, frameDepth/2 + zOffset);
            mainFrameGroup.add(rightJamb);

            // 2. SASH (movable part)
            const sashGroup = new THREE.Group();
            
            // Sash outer dimensions
            const sashOuterWidth = w - jambW - jambW - sashClearance*2;
            const sashOuterHeight = h - frameW - cillW - sashClearance*2;

            // Top rail
            const sashTop = new THREE.Mesh(new THREE.BoxGeometry(sashOuterWidth, railW, sashDepth), sashMaterial);
            sashTop.position.set(0, sashOuterHeight/2 - railW/2, sashDepth/2);
            sashGroup.add(sashTop);

            // Bottom rail
            const sashBottom = new THREE.Mesh(new THREE.BoxGeometry(sashOuterWidth, railW, sashDepth), sashMaterial);
            sashBottom.position.set(0, -sashOuterHeight/2 + railW/2, sashDepth/2);
            sashGroup.add(sashBottom);

            // Left stile
            const sashLeft = new THREE.Mesh(new THREE.BoxGeometry(stileW, sashOuterHeight - railW*2, sashDepth), sashMaterial);
            sashLeft.position.set(-sashOuterWidth/2 + stileW/2, 0, sashDepth/2);
            sashGroup.add(sashLeft);

            // Right stile
            const sashRight = new THREE.Mesh(new THREE.BoxGeometry(stileW, sashOuterHeight - railW*2, sashDepth), sashMaterial);
            sashRight.position.set(sashOuterWidth/2 - stileW/2, 0, sashDepth/2);
            sashGroup.add(sashRight);

            // Glass pane
            const glassW = sashOuterWidth - stileW*2;
            const glassH = sashOuterHeight - railW*2;
            const glass = new THREE.Mesh(new THREE.BoxGeometry(glassW, glassH, 0.01), glassMaterial);
            glass.position.set(0, 0, sashDepth/2 + 0.005);
            sashGroup.add(glass);

            // Position sash within frame
            const sashVerticalOffset = (-h/2 + cillW) + (h - frameW - cillW)/2;
            sashGroup.position.set(0, sashVerticalOffset, zOffset);

            // 3. WORKING HINGES - FIXED ALIGNMENT
            const hingeWidth = 6 * scale;   // 6cm wide
            const hingeHeight = 10 * scale; // 10cm tall
            const hingeThickness = 0.5 * scale; // 0.5cm thick
            
            // Calculate hinge positions based on count (evenly spaced in SASH COORDINATES)
            const hingePositions = [];
            const totalHeight = sashOuterHeight;
            const gap = totalHeight / (hingeCount + 1);
            
            for (let i = 0; i < hingeCount; i++) {
                const y = totalHeight/2 - gap*(i+1);
                hingePositions.push(y);
            }
            
            // CORRECT PIVOT POINT - Front outermost edge of the sash
            const pivotX = hingeSide === "left" ? 
                -w/2 + jambW :  // Left edge of the sash for left hinges
                w/2 - jambW;    // Right edge of the sash for right hinges
            const pivotZ = 0; // FRONT face of the sash
            
            // Create hinge groups
            const frameHingeGroup = new THREE.Group();
            const sashHingeGroup = new THREE.Group();
            
            // Frame hinge parts (attached to main frame)
            const frameHingeDepth = hingeWidth / 2;
            
            // Sash hinge parts (attached to sash)
            const sashHingeDepth = hingeWidth / 2;
            
            // Create hinges for each position - USING SAME COORDINATE SYSTEM
            hingePositions.forEach(sashY => {
                // Convert sash Y coordinate to frame Y coordinate
                const frameY = sashY + sashVerticalOffset;
                
                // Frame hinge part
                const frameHinge = new THREE.Mesh(
                    new THREE.BoxGeometry(frameHingeDepth, hingeHeight, hingeThickness),
                    hardwareMaterial
                );
                
                // Sash hinge part  
                const sashHinge = new THREE.Mesh(
                    new THREE.BoxGeometry(sashHingeDepth, hingeHeight, hingeThickness),
                    hardwareMaterial
                );
                
                if (hingeSide === "left") {
                    // Left hinges - frame hinge on left, sash hinge on right of pivot
                    frameHinge.position.set(-frameHingeDepth/2, frameY, pivotZ);
                    sashHinge.position.set(sashHingeDepth/2, sashY, 0);
                } else {
                    // Right hinges - frame hinge on right, sash hinge on left of pivot
                    frameHinge.position.set(frameHingeDepth/2, frameY, pivotZ);
                    sashHinge.position.set(-sashHingeDepth/2, sashY, 0);
                }
                
                frameHingeGroup.add(frameHinge);
                sashHingeGroup.add(sashHinge);
            });
            
            // Position hinge groups
            frameHingeGroup.position.x = pivotX;
            sashHingeGroup.position.x = pivotX;
            sashHingeGroup.position.z = pivotZ;
            
            // Add hinge groups to their respective parents
            mainFrameGroup.add(frameHingeGroup);
            sashGroup.add(sashHingeGroup);

            // Add all components to main window group
            windowGroup.add(mainFrameGroup);
            windowGroup.add(sashGroup);
            
            // Store references for animation
            windowGroup.userData.sash = sashGroup;
            windowGroup.userData.frameHinges = frameHingeGroup;
            windowGroup.userData.sashHinges = sashHingeGroup;
            windowGroup.userData.originalSashPosition = sashGroup.position.clone();
            windowGroup.userData.originalSashRotation = sashGroup.rotation.clone();
            windowGroup.userData.pivotPoint = new THREE.Vector3(pivotX, 0, pivotZ);
            windowGroup.userData.openDirection = hingeSide === "left" ? 1 : -1;
            
            return windowGroup;
        }

        // Create detailed casement window with all parameters
        const windowParams = {
            widthCm: width,
            heightCm: height,
            frameThicknessCm: thickness,
            frameWidthCm: frameWidth,
            sashWidthCm: sashWidth,
            jambWidthCm: jambWidth,
            stileWidthCm: stileWidth,
            railWidthCm: railWidth,
            cillWidthCm: cillWidth,
            hingeCount: hingeCount,
            hingeSide: hingeSide
        };

        const windowGroup = createDetailedCasementWindow(windowParams);
        windowGroup.position.y = windowPositionY;
        scene.add(windowGroup);
        windowSash = windowGroup.userData.sash;

        // Add click detection for the window
        const raycaster = new THREE.Raycaster();
        const mouse = new THREE.Vector2();

        function onWindowClick(event) {
            const rect = renderer.domElement.getBoundingClientRect();
            mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

            raycaster.setFromCamera(mouse, camera);
            const intersects = raycaster.intersectObject(windowGroup, true);

            if (intersects.length > 0) {
                toggleWindow();
            }
        }

        function toggleWindow() {
            isWindowOpen = !isWindowOpen;
            const targetRotation = isWindowOpen ? Math.PI / 2 : 0;
            
            windowStateElement.textContent = `Window State: ${isWindowOpen ? 'Open' : 'Closed'}`;
            windowStateElement.style.borderLeftColor = isWindowOpen ? '#28a745' : '#dc3545';
            windowStateElement.style.background = isWindowOpen ? 
                'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)';

            const startRotation = windowRotation;
            const duration = 1500;
            const startTime = Date.now();

            function animateSash() {
                const currentTime = Date.now();
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const easeProgress = 1 - Math.pow(1 - progress, 3);
                windowRotation = startRotation + (targetRotation - startRotation) * easeProgress;
                
                // CORRECT FRONT PIVOT ROTATION with direction
                const sash = windowGroup.userData.sash;
                const pivot = windowGroup.userData.pivotPoint;
                const openDirection = windowGroup.userData.openDirection;
                
                // Reset to original position
                sash.position.copy(windowGroup.userData.originalSashPosition);
                sash.rotation.copy(windowGroup.userData.originalSashRotation);
                
                // Rotate around correct front pivot point with direction
                sash.position.sub(pivot);
                sash.rotation.y = windowRotation * openDirection;
                sash.position.applyAxisAngle(new THREE.Vector3(0, 1, 0), windowRotation * openDirection);
                sash.position.add(pivot);

                if (progress < 1) {
                    requestAnimationFrame(animateSash);
                }
            }

            animateSash();
        }

        // Add click event listener
        renderer.domElement.addEventListener('click', onWindowClick);

        // Create interior room
        const createInteriorRoom = (actualWallWidth, wallHeight, wallDepth, actualWallBottom) => {
            const roomGroup = new THREE.Group();
            
            const floorThickness = 10 * scale;
            const floorDepth = 200 * scale;
            
            const floor = new THREE.Mesh(
                new THREE.BoxGeometry(actualWallWidth, floorThickness, floorDepth),
                new THREE.MeshPhongMaterial({ color: 0x8B4513 })
            );
            floor.position.y = actualWallBottom;
            floor.position.z = wallDepth/2 + floorDepth/2;
            roomGroup.add(floor);

            const backWallThickness = 2 * scale;
            
            const backWall = new THREE.Mesh(
                new THREE.BoxGeometry(actualWallWidth, wallHeight, backWallThickness),
                new THREE.MeshPhongMaterial({ color: 0xf5f5f5 })
            );
            backWall.position.z = wallDepth/2 + floorDepth - backWallThickness/2;
            backWall.position.y = actualWallBottom + wallHeight/2;
            roomGroup.add(backWall);

            return roomGroup;
        };

        const interiorRoom = createInteriorRoom(wallWidth, wallHeight, wallDepth, wallBottom);
        scene.add(interiorRoom);

        // IMPROVED CAMERA POSITION - Start from FRONT view
        function setupCamera() {
            // Position camera in front of the window (negative Z = front)
            camera.position.set(0, windowPositionY, -3); // Front view, centered on window
            camera.lookAt(0, windowPositionY, 0); // Look at the window
            
            // Update controls target to focus on the window
            controls.target.set(0, windowPositionY, 0);
            controls.update();
        }

        // Set up camera after everything is created
        setupCamera();

        // Handle window resize
        window.addEventListener('resize', function() {
            const container = document.getElementById('container');
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        });

        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            controls.update();
            renderer.render(scene, camera);
        }

        animate();
    </script>
</body>
</html>
