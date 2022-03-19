function hide() {
    var x = document.getElementById("More");
    var y = document.getElementById("Seemore")
    if (x.style.display === "none") {
        x.style.display = "block";
        y.innerHTML = "See Less";
    } else {
        x.style.display = "none";
        y.innerHTML = "See More";
    }
}

function addNewComment(event){
    event.preventDefault();
    var newcomment = document.getElementById("newComment").value;
    var xhr = new XMLHttpRequest();
    xhr.open("POST","../assets/PHP/comment.php",true);
    xhr.setRequestHeader("Content-Type","application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState != 4 || xhr.status != 200) return;

        // On Success of creating a new Comment
        console.log("Success: " + xhr.responseText);  
        commentForm.reset();
    };
    xhr.send(JSON.stringify(newcomment));
}

function showProgress(filled,amount){
    var width = (filled/amount)*100;
    root = document.documentElement;
    root.style.setProperty('--end-width', width + "%"); 
}

function copylink() {
    var copyText = document.getElementById("sharelink");  
    navigator.clipboard.writeText(copyText.innerHTML);
    alert("Copied the link: " + copyText.innerHTML);
}

function view_share(){
    var z = document.getElementById("smbuttons");
    if (z.style.display === "none") {
        z.style.display = "flex";
    } else {
        z.style.display = "none";
    }

}


// document.addEventListener("DOMContentLoaded", function(event) {
//     //$('#commentForm').on('submit', function(event){
//     document.getElementById("commentForm").addEventListener("submit",function(){
//         event.preventDefault();
//         //var form_data = $(this).sterialize();
//         var newcomment = {

//         }
//         console.log("Hey you!");

//         var xmlhttp = new XMLHttpRequest();

//         xmlhttp.onreadystatechange = function(){
//             if (xmlhttp.readyState == XMLHttpRequest.DONE)//4
//             {
//                 if (xmlhttp.status == 200){
                    
//                 }
//             }
//         }

//         // $.ajax({
//         //     url:"../assets/PHP/comment.php",
//         //     method:"POST",
//         //     data: form_data,
//         //     dataType:"JSON",
//         //     success: function(data){
//         //         if (data.error != '') 
//         //         {
//         //             $('#commentForm')[0].reset();
//         //             $('#newcomment').html(data.error);
//         //         }
//         //     }
//         // })
//     });

// });

