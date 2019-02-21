




// let total = parseInt(document.getElementById('total-payments-number').innerHTML);
// for(let i=1; i<=total; i++) {
//     document.getElementById(`payments-item${i}`).style.backgroundColor = "white";
// }

document.body.onclick= function(e) {
    e = window.event ? event.srcElement : e.target;
    if (e.className && e.className.indexOf('sent') !== -1) {



        // console.clear();
        let item = [];
        for(let i=1; i<=8; i++) {
            item[i] = document.getElementById(`payments-item${i}`).style.backgroundColor;
            // console.log(item[i]);

            if(item[i] === undefined || item[i] === null || item[i] === "") {
                item[i] = "white";
                // console.log(item[i] + "<<<<");
            }
            console.log(item);
        }
        //
        // localStorage.setItem('myElement', item);
        // const retrievedObject = localStorage.getItem('myElement');
        // console.log(JSON.parse(retrievedObject));





        // Sent
        let row = document.getElementById(e.parentElement.parentElement.id);
        if(row.style.backgroundColor === '' || row.style.backgroundColor === "white")
            row.style.backgroundColor = "blue";
        else {
            row.style.backgroundColor = "white";
        }

    }
};
