<?php

// --- Configuration, edit those values as needed
$board = "Testliste";
$lanes = [
    [
        "name" => "Backlog",
        "color" => "#d4d7de",
        "slug" => "backlog"
    ],
    [
        "name" => "In Progress",
        "color" => "#ffe599",
        "slug" => "inprogress"
    ],
    [
        "name" => "Waiting",
        "color" => "#cfe2f3",
        "slug" => "waiting"
    ],
    [
        "name" => "Done",
        "color" => "#b6d7a8",
        "slug" => "done"
    ]
];


// --- Data Handing
$db = new PDO("sqlite:".$board.".sqlite3");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$create = "CREATE TABLE IF NOT EXISTS 'tasks' ( 'id' INTEGER NOT NULL UNIQUE, 'content' TEXT NOT NULL, 'lane' TEXT NOT NULL, 'time' INTEGER NOT NULL, PRIMARY KEY('id','id'));";
$statement = $db->prepare($create)->execute();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload = json_decode(file_get_contents("php://input"), true);

    $db->prepare("DELETE FROM tasks")->execute();
    foreach ($payload as $task) {
        $statement = $db->prepare("INSERT INTO tasks (id, content, lane, time) VALUES (:id, :content, :lane, :time)"); // ON CONFLICT(id) DO UPDATE SET lane = excluded.lane");
        $statement->bindParam(":id", $task["id"]);
        $statement->bindParam(":content", $task["content"]);
        $statement->bindParam(":lane", $task["lane"]);
        $statement->bindParam(":time", $task["time"]);
        $statement->execute();
    }
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$board?></title>
    <link rel="icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJjdXJyZW50Q29sb3IiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgc3Ryb2tlLXdpZHRoPSIyIiBjbGFzcz0iaWNvbiBpY29uLXRhYmxlciBpY29ucy10YWJsZXItb3V0bGluZSBpY29uLXRhYmxlci1jaGVja2JveCI+PHBhdGggc3Ryb2tlPSJub25lIiBkPSJNMCAwaDI0djI0SDB6Ii8+PHBhdGggZD0ibTkgMTEgMyAzIDgtOCIvPjxwYXRoIGQ9Ik0yMCAxMnY2YTIgMiAwIDAgMS0yIDJINmEyIDIgMCAwIDEtMi0yVjZhMiAyIDAgMCAxIDItMmg5Ii8+PC9zdmc+"/>

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #f1f2f4;
            font-family: system-ui, sans-serif;
        }

        main {
            margin: 0 auto;
            max-width: 80rem;
            padding: 2rem;

        }
        h1 {
            font-weight: 400;
            font-size: 1.618rem;
            margin: 0;
            padding: 1rem;
        }

        h2 {
            font-weight: 400;
            font-size: 1rem;
            padding: 1rem;
            margin: 0;
        }

        .lanes {
            display: grid;
            gap: 3rem;
            grid-template-columns: repeat(4, 1fr) 4rem;
        }

        .lane {
            background: none;
            border-radius: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            padding: 1rem;
            min-height: 50svh;
        }

        .lane.hover {
            background: #e3e5e9;
        }

        .lane:hover .todo-new {
            border: 2px dashed #aab0bc;
        }

        .todo {
            background: var(--todoColor, #d4d7de);
            border-radius: 0.5rem;
            padding: 1rem;
            font-size: 14px;
            line-height: 1.3;
        }

        .todo time {
            font-size: 12px;
            margin: 0 0 0.5rem 0;
        }

        .todo-new {
            background: none;
            border-radius: 0.5rem;
            border: 2px dashed #d5d8de;
            padding: 1rem;
            outline: none;
        }

        .todo-new:active,
        .todo-new:focus {
            border: 2px solid #d5d8de;
            outline: none;
        }

        #trash {
            padding: 1rem;
            opacity: 0.15;
        }

    </style>
</head>
<body>

    <main>
        <h1><?=$board?></h1>
        <div class="lanes">
            <?php foreach ($lanes as $i => $lane): ?>
                <div>
                    <h2><?=$lane["name"]?></h2>
                    <div class="lane" data-name="<?=$lane["slug"]?>" style="--todoColor:<?=$lane["color"]?>" ondrop="drop(event)" ondragover="allowDrop(event)">
                        <?php
                        $statement = $db->prepare("SELECT * FROM tasks WHERE lane = ? ORDER BY time");
                        $statement->execute([$lane["slug"]]);
                        $tasks = $statement->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($tasks as $task) {
                            echo "<div id='".$task["id"]."' class='todo' draggable='true' ondragstart='drag(event)'><time data-time='".$task["time"]."'>".date("d.m.Y - H:i:s", $task["time"]/1000)."</time><div class='content'>".nl2br($task["content"])."</div></div>";
                        }
                        ?>
                    </div>
                    <?php if ($i==0): ?>
                        <div class="todo-new" contenteditable></div>
                    <?php endif ?>
                </div>
            <?php endforeach ?>

            <div id="trash" ondrop="dropDelete(event)" ondragover="allowDrop(event)">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-trash"><path fill="none" d="M0 0h24v24H0z"/><path d="M20 6a1 1 0 0 1 .117 1.993L20 8h-.081L19 19a3 3 0 0 1-2.824 2.995L16 22H8c-1.598 0-2.904-1.249-2.992-2.75l-.005-.167L4.08 8H4a1 1 0 0 1-.117-1.993L4 6h16zM14 2a2 2 0 0 1 2 2 1 1 0 0 1-1.993.117L14 4h-4l-.007.117A1 1 0 0 1 8 4a2 2 0 0 1 1.85-1.995L10 2h4z"/></svg>
            </div>
        </div>
    </main>

    <script>
    function allowDrop(ev) {
        ev.preventDefault();
        if (ev.target.classList.contains("lane")) {
            document.querySelectorAll(".lane").forEach(lane => { lane.classList.remove("hover")});
            ev.target.classList.add("hover");
        }
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        if (ev.target.classList.contains("lane")) {
            var data = ev.dataTransfer.getData("text");
            ev.target.append(document.getElementById(data));
            document.querySelectorAll(".lane").forEach(lane => { lane.classList.remove("hover")});
            save();
        }
    }

    function dropDelete(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        document.getElementById(data).remove();
        document.querySelectorAll(".lane").forEach(lane => { lane.classList.remove("hover")});
        save();
    }

    function save() {
        var todos = [];
        document.querySelectorAll(".todo").forEach(todo => {
            var lane = todo.closest(".lane");
            todos.push({
                "id": todo.id,
                "time": todo.querySelector("time").dataset.time,
                "content": todo.querySelector(".content").innerText,
                "lane": lane.dataset.name
            });
        });

        const xhr = new XMLHttpRequest();
        xhr.open("POST", window.location.href);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(todos));
    }

    document.querySelectorAll(".todo-new").forEach(editor => {
        editor.addEventListener("keydown", (event) => {
            if (event.ctrlKey && event.key === "Enter") {
                event.preventDefault();

                const sourceElement = document.activeElement;

                // Create new element
                const newElement = document.createElement("div");
                newElement.id = `item-${Math.random().toString(36).substr(2, 9)}`; // Random ID
                newElement.classList.add("todo");
                newElement.setAttribute("draggable", true);
                newElement.setAttribute("ondragstart", "drag(event)")

                // Create time element with the current time
                const date = new Date();
                const dateString = `${String(date.getDate()).padStart(2, "0")}.${String(date.getMonth() + 1).padStart(2, "0")}.${date.getFullYear()} - ${String(date.getHours()).padStart(2, "0")}:${String(date.getMinutes()).padStart(2, "0")}`;
                const timeElement = document.createElement("time");
                timeElement.dataset.time = Date.now();
                timeElement.textContent = dateString;
                newElement.appendChild(timeElement);

                const contentElement = document.createElement("div");
                contentElement.classList.add("content");
                contentElement.innerHTML = sourceElement.innerHTML;
                newElement.appendChild(contentElement);

                sourceElement.previousElementSibling.append(newElement);
                sourceElement.innerHTML = "";

                save();
            }
        });
    });
    </script>

</body>
</html>
