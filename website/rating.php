<?php
$user = "SQLUSER";
$password = "SQLPWD";
$database = "SQLDBNAME";
$table_tracker = "item_tracker";
$table_banned_ips = "banned_ips";
$raw_clientIP = $_SERVER['REMOTE_ADDR'];
$clientIP = preg_replace('/[^0-9.:]/', '', $raw_clientIP);
$number_of_items = 64;
$size_local_item_pool = 192;

try {
    $raw_userID = $_GET['userID'];
    $userID = preg_replace('/[^0-9a-z]/', '', $raw_userID);

    $db = new PDO("mysql:host=localhost;dbname=$database", $user, $password);
    $item_pool = $db->query("SELECT itemID, isequence, no_ratings FROM $table_tracker ORDER BY no_ratings ASC LIMIT $size_local_item_pool")->fetchAll(PDO::FETCH_ASSOC);
    $banned_ips = $db->query("SELECT clientIP FROM $table_banned_ips")->fetchAll(PDO::FETCH_COLUMN);

    if (count($item_pool) < $size_local_item_pool) {
        echo "Error!: Not enough items in the pool.<br/>";
        die();
    }
    $randomSQLRows = array_rand($item_pool, $number_of_items);
    foreach ($randomSQLRows as $index) {
        $randomSQLRowsArray[] = $item_pool[$index];
    }
    $randomSQLRowsArrayJSON = json_encode($randomSQLRowsArray);
    $db = null; # close connection
} catch (PDOException $e) {
    echo "Error!: " . $e->getMessage() . "<br/>";
    die();
}

if (strlen($userID) != 12 || in_array($clientIP, $banned_ips)) {
    header('Location: rating.html');
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="shortcut icon" href="#">
<title>Study</title>
</head>

<body onload = initialise()>
<table style="text-align: left; width: 760px; height: 450px;">
    <tbody>
    <tr> 
        <td style="height: 50px; width: 50px;"></td>
        <td style="height: 50px; width: 660px;"></td>
        <td style="height: 50px; width: 50px;"></td>
    </tr>
    <tr>
        <td style="vertical-align: top; text-align: center; height: 250px; width: 50px;" id="counter_cell"></td>
        <td style="vertical-align: middle; text-align: center; height: 250px; width: 660px; font-size: 18px;" id="sequence_cell"></td>
        <td style="text-align: center; height: 250px; width: 50px;"></td>
    </tr>
    <tr>
        <td style="height: 75px; width: 50px;"></td>
        <td style="vertical-align: middle; text-align: center; height: 75px; width: 660px;">
        <img onclick="rating(1)" style="width: 35px; height: 35px;" alt="button1" src="button1.jpg" id="button1">
        &nbsp;&nbsp;
        <img onclick="rating(2)" style="width: 35px; height: 35px;" alt="button2" src="button2.jpg" id="button2">
        &nbsp;&nbsp;
        <img onclick="rating(3)" style="width: 35px; height: 35px;" alt="button3" src="button3.jpg" id="button3">
        &nbsp;&nbsp;
        <img onclick="rating(4)" style="width: 35px; height: 35px;" alt="button4" src="button4.jpg" id="button4">
        &nbsp;&nbsp;
        <img onclick="rating(5)" style="width: 35px; height: 35px;" alt="button5" src="button5.jpg" id="button5">
        &nbsp;&nbsp;
        <img onclick="rating(6)" style="width: 35px; height: 35px;" alt="button6" src="button6.jpg" id="button6">
        &nbsp;&nbsp;
        <img onclick="rating(7)" style="width: 35px; height: 35px;" alt="button7" src="button7.jpg" id="button7">
        </td>
        <td style="height: 75px; width: 50px;"></td>
    </tr>
    <tr>
        <td style="height: 75px; width: 50px;"></td>
        <td style="vertical-align: top; text-align: center; height: 75px; width: 660px;" id="scale_legend">
        unnatural/ungrammatical &lt;&lt;&lt; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &gt;&gt;&gt; natural/grammatical &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td style="height: 75px; width: 50px;"></td>
    </tr>
    </tbody>
</table>


<script>
var userID = "<?php echo $userID; ?>";
var sequencesArray = <?php echo $randomSQLRowsArrayJSON; ?>;
// shuffle sequencesArray:
for (let i = sequencesArray.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [sequencesArray[i], sequencesArray[j]] = [sequencesArray[j], sequencesArray[i]];
}


const default_legend = document.getElementById("scale_legend").innerHTML;

var number_of_items = 0;
var item_counter = 0;
var itemID = 0;
var time_item_loaded = 0;
var rating_time = 0;
var mogl_zeit = 0;
var mogl_habdich = 0;
var threshold = 0;
var langprofic_ratings = [];


function initialise() {
    addExtraItems();
    newItem();
}

function addExtraItems() {
    // gotcha items
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 200001, "isequence": "Can you click on the leftmost button, the one with the x?", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 200002, "isequence": "Please click on the button with the x, the leftmost one.", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 200003, "isequence": "Click on the rightmost button, the one with the checkmark.", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 200004, "isequence": "Please click on the checkmark button (rightmost).", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 200005, "isequence": "Click on the yellow button in the centre of the scale.", "no_ratings": 0});

    // language proficiency items
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 300001, "isequence": "Noah is less than Kim is fit tall.", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 300002, "isequence": "Leo not very good the student is.", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 300003, "isequence": "Rosie seems to be intelligent.", "no_ratings": 0});
    sequencesArray.splice(randomarrpos(), 0, {"itemID": 300004, "isequence": "Isla continued to write a paper.", "no_ratings": 0});

    // scale calibration items
    sequencesArray.unshift({"itemID": 100001, "isequence": "Mary was annoyed because son embarrassed pictures has.", "no_ratings": 0});
    sequencesArray.unshift({"itemID": 100003, "isequence": "Louise liked the place we just ran past.", "no_ratings": 0});
    sequencesArray.unshift({"itemID": 100002, "isequence": "Mark as sleepy the student is as Alex.", "no_ratings": 0});
    sequencesArray.unshift({"itemID": 100004, "isequence": "John was arrested.", "no_ratings": 0});

    number_of_items = sequencesArray.length;
}

function randomarrpos() {
    return Math.floor((sequencesArray.length * 0.33) + (Math.random() * sequencesArray.length * 0.63)); // 0.63 instead of 0.67 to avoid array gaps in small item pools
}

function newItem() {
    if (item_counter < number_of_items) {
        itemID = sequencesArray[item_counter].itemID;
        document.getElementById("sequence_cell").innerHTML = sequencesArray[item_counter].isequence;
        document.getElementById("counter_cell").innerHTML = (item_counter + 1) + "/" + number_of_items;
        time_item_loaded = new Date().getTime();
        threshold = Math.floor(((sequencesArray[item_counter].isequence.length * 25) + 225)/2);
    } else {
        document.getElementById("scale_legend").innerHTML = "You will be redirected shortly.";
        document.getElementById("counter_cell").innerHTML = "~";
        hideAllImages();
        check_langprofic_items();
        setTimeout(function() {window.location = "done.php?userID=" + userID},2000);
    }
    item_counter += 1;
}

function rating(rating) {
    rating_time = (new Date().getTime()) - time_item_loaded;
    if (rating_time < threshold) {
        mogl_zeit += 1;
        moglAction("You are going too fast.");
    }
    if ((itemID == 200001 && rating > 2) || (itemID == 200002 && rating > 2) || (itemID == 200003 && rating < 6) || (itemID == 200004 && rating < 6) || (itemID == 200005 && rating != 4)) {
        mogl_habdich += 1;
        moglAction("Please pay attention to the study.");
    }
    if (itemID == 300001 || itemID == 300002 || itemID == 300003 || itemID == 300004) {
        langprofic_ratings[itemID] = rating;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "WEBSITELOCATION/submit_rating.php?userID=" + userID + "&itemID=" + itemID + "&rating=" + rating + "&msecs=" + rating_time, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                // console.log("Rating submitted.");
                //pass
            } else {
                console.log("Rating failed.")
            }
        }
        };
    xhr.send();
    
    if (mogl_zeit > 3) {
        known_user(0);
        error();
    } else if (mogl_habdich > 2) {
        known_user(1);
        error();
    } else {
        newItem();
    }
}

function hideAllImages() {
    for (let i = 1; i < 8; i++) {
        document.getElementById("button" + i).style.visibility = "hidden";
    }
    document.getElementById("sequence_cell").style.visibility = "hidden";
}

function showAllImages() {
    for (let i = 1; i < 8; i++) {
        document.getElementById("button" + i).style.visibility = "visible";
    }
    document.getElementById("sequence_cell").style.visibility = "visible";
    time_item_loaded = new Date().getTime();
}

function moglAction(warning_message) {
    hideAllImages();
    document.getElementById("scale_legend").innerHTML = warning_message;
    if ((mogl_zeit < 4) && (mogl_habdich < 3)) {
        setTimeout(function() {showAllImages();},2000);
        setTimeout(function() {document.getElementById("scale_legend").innerHTML = default_legend;},2000);
    }
}

function known_user(category) {
    var xhr = new XMLHttpRequest();
        xhr.open("GET", "WEBSITELOCATION/submit_user.php?userID=" + userID + "&category=" + category, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // console.log("User submitted.");
                    //pass
                } else {
                    console.log("User submission failed.")
                }
            }
            };
        xhr.send();
}

function error() {
    hideAllImages();
    document.getElementById("scale_legend").innerHTML = `THANK YOU, please return to <a href="PROLIFICLOCATION">PROLIFICLOCATION</a> or use the following completion code directly: PROLIFICCODE .`;
    document.getElementById("counter_cell").innerHTML = "~";
}

function check_langprofic_items() {
    bad_langprofic_items = langprofic_ratings[300001] + langprofic_ratings[300002];
    good_langprofic_items = langprofic_ratings[300003] + langprofic_ratings[300004];
    if ((bad_langprofic_items > 7) || (good_langprofic_items < 9)) {
        known_user(2);
    }
}

</script>

</body>
</html>
