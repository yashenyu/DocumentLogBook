<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Document LogBook</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-container {
            max-width: 1000px;
            margin: 4rem auto;
            padding: 2rem;
            color: #1e293b;
        }
        .about-card {
            background: white;
            border-radius: 1.5rem;
            padding: 3rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .about-header {
            margin-bottom: 3rem;
        }
        .about-header h1 {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .about-header p {
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .developers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .dev-card {
            padding: 2rem 1.5rem;
            background: #fcfcfc;
            border: 1px solid #f1f5f9;
            border-radius: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .dev-card:hover {
            transform: translateY(-10px);
            border-color: var(--accent-color);
            background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        
        .dev-avatar {
            width: 100px;
            height: 100px;
            background: #f1f5f9;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 2rem;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #f1f5f9;
        }
        
        .dev-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        .dev-role {
            font-size: 0.85rem;
            color: var(--accent-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 4rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-back:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }
    </style>
</head>
<body style="background-color: #f8fafc;">
    <div class="about-container">
        <div class="about-card">
            <div class="about-header">
                <h1>Meet the Developers</h1>
                <p>The creative minds behind the Document Logbook system.</p>
            </div>
            
            <div class="developers-grid">
                <!-- Developer 1 -->
                <a class="dev-card" href="https://github.com/yashenyu" target="_blank" rel="noopener noreferrer">
                    <div class="dev-avatar">
                        <i class="fa-solid fa-code"></i>
                    </div>
                    <div class="dev-name">Mark Aaron B. Dayrit</div>
                    <div class="dev-role">Web Developer</div>
                </a>

                <!-- Developer 2 -->
                <a class="dev-card" href="https://github.com/Jasper-Andrew-L-Chan" target="_blank" rel="noopener noreferrer">
                    <div class="dev-avatar">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <div class="dev-name">Jasper Andrew L. Chan</div>
                    <div class="dev-role">Frontend Developer</div>
                </a>

                <!-- Developer 3 -->
                <a class="dev-card" href="https://github.com/carlosiron" target="_blank" rel="noopener noreferrer">
                    <div class="dev-avatar">
                        <i class="fa-solid fa-palette"></i>
                    </div>
                    <div class="dev-name">Carlo T. Siron</div>
                    <div class="dev-role">UI/UX Designer</div>
                </a>

                <!-- Developer 4 -->
                <a class="dev-card" href="https://github.com/CSiron21" target="_blank" rel="noopener noreferrer">
                    <div class="dev-avatar">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div class="dev-name">Clark T. Siron</div>
                    <div class="dev-role">Backend Specialist</div>
                </a>
            </div>
            
            <a href="login.php" class="btn-back">
                &lsaquo; Back to Main
            </a>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="blob blob-1" style="background: var(--primary-color); opacity: 0.05;"></div>
    <div class="blob blob-2" style="background: var(--accent-color); opacity: 0.05;"></div>
    
    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
