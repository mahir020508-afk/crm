<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM PRO | Yönetim Paneli</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            black: '#090d16',
                            dark: '#0f172a',
                            electric: '#0052ff',
                            electricHover: '#0041cc',
                            turquoise: '#00f2fe',
                            cyan: '#06b6d4'
                        }
                    },
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        button.dt-button, div.dt-button, a.dt-button {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 0.45rem 1rem !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
            box-shadow: none !important;
            transition: all 0.2s ease !important;
        }
        button.dt-button:hover, div.dt-button:hover, a.dt-button:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }
        div.dt-buttons {
            display: flex !important;
            gap: 0.5rem !important;
            margin-bottom: 0 !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            padding: 0.45rem 1rem !important;
            outline: none !important;
            font-size: 0.825rem !important;
            background-color: #ffffff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0052ff !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            border: none !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans flex min-h-screen">