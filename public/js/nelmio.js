document.addEventListener('DOMContentLoaded', function() {
    let container_infos = document.querySelector(".info");
    let main = document.querySelector(".main");
    let description = document.querySelector(".description");
    let logo = document.querySelector(".logo-api");

    let container_header = document.createElement("div");

    container_infos.appendChild(logo);

    container_header.appendChild(main);
    container_header.appendChild(description);
    container_infos.appendChild(container_header);
});