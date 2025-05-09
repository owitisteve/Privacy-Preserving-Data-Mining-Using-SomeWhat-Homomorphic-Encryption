<?php
function showModal($message) {
    echo '
    <div id="universalModal" class="modal">
      <div class="modal-content">
        <h4>Notice</h4>
        <p>' . $message . '</p>
        <button onclick="redirectToDashboard()">OK</button>
      </div>
    </div>

    <style>
    .modal {
      display: block;
      position: fixed;
      z-index: 1000;
      padding-top: 20%;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
      background-color: #fff;
      margin: auto;
      padding: 20px;
      border-radius: 12px;
      width: 40%;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .modal-content h4 {
      margin-bottom: 10px;
    }

    .modal-content button {
      margin-top: 15px;
      padding: 8px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .modal-content button:hover {
      background-color: #0056b3;
    }
    </style>

    <script>
    function redirectToDashboard() {
        window.location.href = "user_dashboard.php";
    }
    </script>
    ';
}
?>
