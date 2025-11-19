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
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="number"] {
            width: 200px;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="number"]:focus {
            outline: none;
            border-color: #007bff;
        }
        button {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }
        #container {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
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
    </style>
</head>
<body>
    <div class="controls">
        <h1>🏠 3D Window Generator</h1>
        <p>Enter dimensions to generate a custom 3D window in a stone wall:</p>
        
        <form method="POST">
            <div class="form-group">
                <label for="width">Width (cm):</label>
                <input type="number" id="width" name="width" value="<?php echo isset($_POST['width']) ? htmlspecialchars($_POST['width']) : '100'; ?>" min="10" max="500" step="1" required>
            </div>
            
            <div class="form-group">
                <label for="height">Height (cm):</label>
                <input type="number" id="height" name="height" value="<?php echo isset($_POST['height']) ? htmlspecialchars($_POST['height']) : '150'; ?>" min="10" max="500" step="1" required>
            </div>
            
            <div class="form-group">
                <label for="thickness">Frame Thickness (cm):</label>
                <input type="number" id="thickness" name="thickness" value="<?php echo isset($_POST['thickness']) ? htmlspecialchars($_POST['thickness']) : '5'; ?>" min="1" max="20" step="0.5" required>
            </div>
            
            <button type="submit">Generate 3D Window</button>
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

        // Enhanced Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);

        const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(5, 5, 5);
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

        // Create detailed casement window with NORMAL COLORS and NO HANDLE
        function createDetailedCasementWindow(windowWidth, windowHeight, frameThickness, windowPositionY) {
            const windowGroup = new THREE.Group();
            
            const w = windowWidth * scale;
            const h = windowHeight * scale;
            const t = frameThickness * scale;
            
            // Normal colors
            const frameMaterial = new THREE.MeshPhongMaterial({ 
                color: 0x8B4513,
                shininess: 30
            });
            
            const sashMaterial = new THREE.MeshPhongMaterial({ 
                color: 0xDEB887,
                shininess: 40
            });
            
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

            // DIMENSIONS - FIXED to prevent clipping
            const zOffset = 0.1 * scale;
            const frameDepth = t * 1.5;
            const sashDepth = t;
            const frameWidth = t * 1.2;
            const sashWidth = t * 0.8;
            const sashClearance = 0.2 * scale; // Proper clearance

            // 1. MAIN FRAME (fixed to wall)
            const mainFrameGroup = new THREE.Group();
            
            // Frame pieces - FRONT FACING (positive Z is front)
            const topFrame = new THREE.Mesh(new THREE.BoxGeometry(w, frameWidth, frameDepth), frameMaterial);
            topFrame.position.y = h/2 - frameWidth/2;
            topFrame.position.z = frameDepth/2 + zOffset;
            mainFrameGroup.add(topFrame);

            const bottomFrame = new THREE.Mesh(new THREE.BoxGeometry(w, frameWidth, frameDepth), frameMaterial);
            bottomFrame.position.y = -h/2 + frameWidth/2;
            bottomFrame.position.z = frameDepth/2 + zOffset;
            mainFrameGroup.add(bottomFrame);

            const leftFrame = new THREE.Mesh(new THREE.BoxGeometry(frameWidth, h - 2*frameWidth, frameDepth), frameMaterial);
            leftFrame.position.x = -w/2 + frameWidth/2;
            leftFrame.position.z = frameDepth/2 + zOffset;
            mainFrameGroup.add(leftFrame);

            const rightFrame = new THREE.Mesh(new THREE.BoxGeometry(frameWidth, h - 2*frameWidth, frameDepth), frameMaterial);
            rightFrame.position.x = w/2 - frameWidth/2;
            rightFrame.position.z = frameDepth/2 + zOffset;
            mainFrameGroup.add(rightFrame);

            // 2. SASH (movable part) - SIMPLIFIED (no cross bars)
            const sashGroup = new THREE.Group();
            
            // Calculate sash dimensions to fit perfectly within frame with proper clearance
            const sashOuterWidth = w - frameWidth*2 - sashClearance*2;
            const sashOuterHeight = h - frameWidth*2 - sashClearance*2;
            
            // Sash frame - CORRECTED dimensions
            const sashTop = new THREE.Mesh(new THREE.BoxGeometry(sashOuterWidth, sashWidth, sashDepth), sashMaterial);
            sashTop.position.y = sashOuterHeight/2 - sashWidth/2;
            sashTop.position.z = sashDepth/2;
            sashGroup.add(sashTop);

            const sashBottom = new THREE.Mesh(new THREE.BoxGeometry(sashOuterWidth, sashWidth, sashDepth), sashMaterial);
            sashBottom.position.y = -sashOuterHeight/2 + sashWidth/2;
            sashBottom.position.z = sashDepth/2;
            sashGroup.add(sashBottom);

            const sashLeft = new THREE.Mesh(new THREE.BoxGeometry(sashWidth, sashOuterHeight - sashWidth*2, sashDepth), sashMaterial);
            sashLeft.position.x = -sashOuterWidth/2 + sashWidth/2;
            sashLeft.position.z = sashDepth/2;
            sashGroup.add(sashLeft);

            const sashRight = new THREE.Mesh(new THREE.BoxGeometry(sashWidth, sashOuterHeight - sashWidth*2, sashDepth), sashMaterial);
            sashRight.position.x = sashOuterWidth/2 - sashWidth/2;
            sashRight.position.z = sashDepth/2;
            sashGroup.add(sashRight);

            // Sash glass - CORRECTED to fit within sash frame
            const glassWidth = sashOuterWidth - sashWidth*2;
            const glassHeight = sashOuterHeight - sashWidth*2;
            
            const sashGlass = new THREE.Mesh(
                new THREE.BoxGeometry(glassWidth, glassHeight, 0.01), 
                glassMaterial
            );
            sashGlass.position.z = sashDepth/2 + 0.005;
            sashGroup.add(sashGlass);

            // Position sash within frame - FRONT FACING
            sashGroup.position.z = zOffset; // Front aligned with frame front

            // 3. WORKING HINGES - Split into frame and sash parts (NO PIVOT POINTS)
            const hingeWidth = 6 * scale;   // 6cm wide
            const hingeHeight = 10 * scale; // 10cm tall
            const hingeThickness = 0.5 * scale; // 0.5cm thick
            
            // Calculate hinge positions (top 1/4 and bottom 1/4 from edges)
            const topHingeY = h/2 - frameWidth - hingeHeight/2 - (h * 0.1); // 10% from top
            const bottomHingeY = -h/2 + frameWidth + hingeHeight/2 + (h * 0.1); // 10% from bottom
            
            // CORRECT PIVOT POINT - Front outermost edge of the sash
            const pivotX = -w/2 + frameWidth; // Left edge of the sash
            const pivotZ = 0; // FRONT face of the sash
            
            // Create hinge groups
            const frameHingeGroup = new THREE.Group();
            const sashHingeGroup = new THREE.Group();
            
            // Frame hinge parts (attached to main frame)
            const frameHingeDepth = hingeWidth / 2;
            const topFrameHinge = new THREE.Mesh(
                new THREE.BoxGeometry(frameHingeDepth, hingeHeight, hingeThickness),
                hardwareMaterial
            );
            topFrameHinge.position.set(-frameHingeDepth/2, topHingeY, pivotZ);
            frameHingeGroup.add(topFrameHinge);
            
            const bottomFrameHinge = new THREE.Mesh(
                new THREE.BoxGeometry(frameHingeDepth, hingeHeight, hingeThickness),
                hardwareMaterial
            );
            bottomFrameHinge.position.set(-frameHingeDepth/2, bottomHingeY, pivotZ);
            frameHingeGroup.add(bottomFrameHinge);
            
            // Sash hinge parts (attached to sash)
            const sashHingeDepth = hingeWidth / 2;
            const topSashHinge = new THREE.Mesh(
                new THREE.BoxGeometry(sashHingeDepth, hingeHeight, hingeThickness),
                hardwareMaterial
            );
            topSashHinge.position.set(sashHingeDepth/2, topHingeY, 0);
            sashHingeGroup.add(topSashHinge);
            
            const bottomSashHinge = new THREE.Mesh(
                new THREE.BoxGeometry(sashHingeDepth, hingeHeight, hingeThickness),
                hardwareMaterial
            );
            bottomSashHinge.position.set(sashHingeDepth/2, bottomHingeY, 0);
            sashHingeGroup.add(bottomSashHinge);
            
            // Position hinge groups
            frameHingeGroup.position.x = pivotX;
            sashHingeGroup.position.x = pivotX;
            sashHingeGroup.position.z = pivotZ;
            
            // Add hinge groups to their respective parents
            mainFrameGroup.add(frameHingeGroup);
            sashGroup.add(sashHingeGroup);

            // REMOVED THE WINDOW HANDLE

            // Add all components to main window group
            windowGroup.add(mainFrameGroup);
            windowGroup.add(sashGroup);
            
            // Position the entire window group in the wall
            windowGroup.position.y = windowPositionY;
            
            // Store references for animation - CORRECT FRONT PIVOT POINT
            windowGroup.userData.sash = sashGroup;
            windowGroup.userData.frameHinges = frameHingeGroup;
            windowGroup.userData.sashHinges = sashHingeGroup;
            windowGroup.userData.originalSashPosition = sashGroup.position.clone();
            windowGroup.userData.originalSashRotation = sashGroup.rotation.clone();
            windowGroup.userData.pivotPoint = new THREE.Vector3(pivotX, 0, pivotZ);
            
            return windowGroup;
        }

        // Create detailed casement window
        const windowGroup = createDetailedCasementWindow(width, height, thickness, windowPositionY);
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
                
                // CORRECT FRONT PIVOT ROTATION
                const sash = windowGroup.userData.sash;
                const pivot = windowGroup.userData.pivotPoint;
                
                // Reset to original position
                sash.position.copy(windowGroup.userData.originalSashPosition);
                sash.rotation.copy(windowGroup.userData.originalSashRotation);
                
                // Rotate around correct front pivot point
                sash.position.sub(pivot);
                sash.rotation.y = windowRotation;
                sash.position.applyAxisAngle(new THREE.Vector3(0, 1, 0), windowRotation);
                sash.position.add(pivot);

                if (progress < 1) {
                    requestAnimationFrame(animateSash);
                }
            }

            animateSash();
        }

        // Add click event listener
        renderer.domElement.addEventListener('click', onWindowClick);

        // Create interior room with normal colors
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

        // Position camera for better view
        camera.position.set(2, 0, 3);
        camera.lookAt(0, 0, 0);

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
