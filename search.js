const pages = [
    { name: "Home", url: "index.html" },
    { name: "About Us", url: "about.html" },
    { name: "Contact", url: "Contact.html" }
  ];

  function searchPages() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const resultsBox = document.getElementById("searchResults");

    resultsBox.innerHTML = "";

    if (input === "") return;

    const filteredPages = pages.filter(page =>
      page.name.toLowerCase().includes(input)
    );

    filteredPages.forEach(page => {
      const div = document.createElement("div");
      div.textContent = page.name;
      div.classList.add("search-item");

      div.onclick = () => {
        window.location.href = page.url;
      };

      resultsBox.appendChild(div);
    });
  }