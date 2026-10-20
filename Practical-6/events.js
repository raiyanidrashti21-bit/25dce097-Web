let allEvents = [];

let currentPage = 1;

let eventsPerPage = 5;


// Fetch JSON Data

fetch("events.json")

    .then(response => {

        if (!response.ok) {
            throw new Error("Failed to load JSON file");
        }

        return response.json();

    })

    .then(data => {

        allEvents = data;

        displayEvents();

    })

    .catch(error => {

        document.getElementById("eventsContainer").innerHTML =
            "Error loading events.";

        console.log(error);

    });


// Display Events

function displayEvents() {

    let container = document.getElementById("eventsContainer");

    container.innerHTML = "";

    let filteredEvents = getFilteredEvents();

    if (filteredEvents.length === 0) {

        container.innerHTML = "<p>No events found.</p>";

        return;

    }


    // Pagination Logic

    
    let start = (currentPage - 1) * eventsPerPage;

    let end = start + eventsPerPage;

    let pageEvents = filteredEvents.slice(start, end);


    // Dynamic Rendering

    pageEvents.forEach(event => {

        container.innerHTML += `

            <div>

                <h3>${event.name}</h3>

                <p>Category: ${event.category}</p>

                <p>Date: ${event.date}</p>

                <p>Venue: ${event.venue}</p>

            </div>

        `;

    });


    displayPagination(filteredEvents);

}


// Search, Filter and Sort

function getFilteredEvents() {

    let searchText =
        document.getElementById("searchInput").value.toLowerCase();

    let category =
        document.getElementById("categoryFilter").value;

    let sortValue =
        document.getElementById("sortSelect").value;


    // Filter Events

    let filteredEvents = allEvents.filter(event => {

        let matchesSearch =
            event.name.toLowerCase().includes(searchText);

        let matchesCategory =
            category === "All" ||
            event.category === category;

        return matchesSearch && matchesCategory;

    });


    // Sorting

    if (sortValue === "name") {

        filteredEvents.sort((a, b) =>
            a.name.localeCompare(b.name)
        );

    }


    if (sortValue === "date") {

        filteredEvents.sort((a, b) =>
            new Date(a.date) - new Date(b.date)
        );

    }


    return filteredEvents;

}


// Pagination

function displayPagination(events) {

    let totalPages =
        Math.ceil(events.length / eventsPerPage);


    let pagination = document.createElement("div");

    pagination.id = "pagination";


    for (let i = 1; i <= totalPages; i++) {

        let button = document.createElement("button");

        button.innerText = i;


        button.onclick = function () {

            currentPage = i;

            displayEvents();

        };


        pagination.appendChild(button);

    }


    document.getElementById("eventsContainer")
        .appendChild(pagination);

}


// Search Event Listener

document.getElementById("searchInput")
    .addEventListener("input", function () {

        currentPage = 1;

        displayEvents();

    });


// Category Filter Listener

document.getElementById("categoryFilter")
    .addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });


// Sort Listener

document.getElementById("sortSelect")
    .addEventListener("change", function () {

        currentPage = 1;

        displayEvents();

    });