function showDiv(event, id) {
    var baseUrl = 'https://fakestoreapi.com/users/';
    var url = baseUrl.concat(id);

    console.log(url);
    //https://fakestoreapi.com/users/1 consoleba kiíródik
    fetch(url)
        .then(res => res.json())
        .then(json => {
            var jsonData = json; // A JSON adat átadása egy változónak
            var hoverDiv = document.getElementById("mainDiv");


            hoverDiv.style.display = "block";
            hoverDiv.style.position = "absolute";
            hoverDiv.style.backgroundColor = "#f9f9f9";
            hoverDiv.style.border = "1px solid #ccc";
            hoverDiv.style.padding = "5px";
            hoverDiv.style.borderRadius = "5px";
            hoverDiv.style.boxShadow = "2px 2px 5px rgba(0,0,0,0.2)";
            hoverDiv.style.zIndex = "9999";
            hoverDiv.style.fontSize = "12px";
            hoverDiv.style.maxWidth = "200px";
            hoverDiv.style.whiteSpace = "nowrap";


            console.log(hoverDiv);
            hoverDiv.classList.add("red"); // Osztály hozzáadása
            //hoverDiv.className += ' tooltip';
            //hoverDiv.setAttribute('class', hoverDiv.getAttribute('class') + 'tooltip');
            /** var classes = hoverDiv.getAttribute('class');
                classes += ' tooltip';
                hoverDiv.setAttribute('class', classes);
             * 
            */

            // Adatok megjelenítése
            hoverDiv.innerHTML = `
                <p>ID: ${jsonData.id}</p>
                <p>Username: ${jsonData.username}</p>
                <p>Email: ${jsonData.email}</p>
                <p>Password: ${jsonData.password}</p>
                <p>First Name: ${jsonData.name.firstname}</p>
                <p>Last Name: ${jsonData.name.lastname}</p>
                <p>Phone: ${jsonData.phone}</p>
                <p>Address:</p>
                <ul>
                    <li>City: ${jsonData.address.city}</li>
                    <li>Street: ${jsonData.address.street}</li>
                    <li>Number: ${jsonData.address.number}</li>
                    <li>Zipcode: ${jsonData.address.zipcode}</li>
                </ul>
            `;
        });
}

function hideDiv() {
    var hoverDiv = document.getElementById("hoverDiv");
    //hoverDiv.style.display = "none";
    hoverDiv.classList.remove("red");
}


