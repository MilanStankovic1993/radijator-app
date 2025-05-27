<x-filament::page>
    <div class="space-y-6">

        <!-- Dodatni alati kartica sa toggle -->
        <div class="max-w-md p-6 rounded-lg shadow-md border mx-auto
                    bg-white dark:bg-gray-800
                    border-gray-200 dark:border-gray-700
                    text-gray-900 dark:text-gray-100">
            <h2 
                id="toggleTitle"
                class="text-2xl font-bold mb-4 cursor-pointer select-none flex justify-between items-center"
            >
                Dodatni alati
                <svg id="toggleIcon" class="w-6 h-6 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </h2>

            <div id="toggleContent">
                <!-- Kalkulator popusta -->
                <label for="originalPrice" class="block mb-1 font-medium">Originalna cena (RSD):</label>
                <input type="number" id="originalPrice" placeholder="Unesi cenu" min="0" step="0.01"
                       class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2
                              focus:ring-blue-500
                              border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700
                              text-gray-900 dark:text-gray-100"/>

                <label for="discountPercent" class="block mb-1 font-medium">Procenat popusta (%):</label>
                <input type="number" id="discountPercent" placeholder="Unesi popust" min="0" max="100" step="0.01"
                       class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2
                              focus:ring-blue-500
                              border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700
                              text-gray-900 dark:text-gray-100"/>

                <button id="calcBtn"
                    class="w-full py-2 font-semibold rounded transition
                           bg-blue-600 hover:bg-blue-700
                           text-black dark:text-white
                           dark:bg-blue-500 dark:hover:bg-blue-600
                           border-2 border-black dark:border-white
                           focus:outline-none focus:ring-2 focus:ring-blue-400 mb-6">
                    Izračunaj popust
                </button>

                <div id="result" class="mb-8 font-semibold"></div>

                <!-- Kalkulator vremena za završetak zadatka -->
                <h3 class="text-xl font-semibold mb-2">Kalkulator vremena za završetak zadatka</h3>

                <label for="availableHours" class="block mb-1 font-medium">Sati na raspolaganju:</label>
                <input type="number" id="availableHours" placeholder="Unesi broj sati" min="0" step="0.1"
                       class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2
                              focus:ring-green-500
                              border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700
                              text-gray-900 dark:text-gray-100"/>

                <label for="taskTime" class="block mb-1 font-medium">Vreme za jedan zadatak (u minutima):</label>
                <input type="number" id="taskTime" placeholder="Unesi trajanje jednog zadatka" min="0" step="1"
                       class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2
                              focus:ring-green-500
                              border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700
                              text-gray-900 dark:text-gray-100"/>

                <label for="breakMinutes" class="block mb-1 font-medium">Ukupno pauze (u minutima):</label>
                <input type="number" id="breakMinutes" placeholder="Unesi ukupno pauza" min="0" step="1"
                       class="w-full p-2 mb-4 border rounded focus:outline-none focus:ring-2
                              focus:ring-green-500
                              border-gray-300 dark:border-gray-600
                              bg-white dark:bg-gray-700
                              text-gray-900 dark:text-gray-100"/>

                <button id="timeCalcBtn"
                    class="w-full py-2 font-semibold rounded transition
                           bg-green-600 hover:bg-green-700
                           text-black dark:text-white
                           dark:bg-green-500 dark:hover:bg-green-600
                           border-2 border-black dark:border-white
                           focus:outline-none focus:ring-2 focus:ring-green-400">
                    Izračunaj broj zadataka
                </button>

                <div id="timeResult" class="mt-4 font-semibold"></div>
            </div>
        </div>

        <!-- Tvoj postojeći sadržaj -->
        <div class="flex justify-center">
            <img src="{{ asset('storage/radijator-inzenjering-40.jpg') }}" alt="Radijator inženjering" class="rounded shadow-md" />
        </div>

        <h1 class="text-3xl font-bold dark:text-gray-100">Dobrodošli u Radijator inženjering</h1>

        <p class="text-lg max-w-prose leading-relaxed dark:text-gray-300">
            Kompanija <strong>Radijator inženjering</strong> d.o.o. u poslovnom smislu je pravni naslednik zanatske radnje
            „Radijator“ koja je osnovana 1991. godine, čija je osnovna delatnost bila montaža i održavanje centralnog grejanja
            čime se i danas bavimo. Prvi toplovodni kotao na čvrsto gorivo izradili smo 1985. godine.
        </p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleTitle = document.getElementById('toggleTitle');
            const toggleContent = document.getElementById('toggleContent');
            const toggleIcon = document.getElementById('toggleIcon');
            let open = true;

            toggleTitle.addEventListener('click', () => {
                open = !open;
                toggleContent.style.display = open ? 'block' : 'none';
                toggleIcon.style.transform = open ? 'rotate(0deg)' : 'rotate(-180deg)';
            });

            // Kalkulator popusta
            document.getElementById('calcBtn').addEventListener('click', () => {
                const price = parseFloat(document.getElementById('originalPrice').value);
                const discount = parseFloat(document.getElementById('discountPercent').value);

                if (isNaN(price) || price < 0) {
                    alert('Molimo unesi validnu originalnu cenu.');
                    return;
                }
                if (isNaN(discount) || discount < 0 || discount > 100) {
                    alert('Molimo unesi validan procenat popusta (0-100).');
                    return;
                }

                const discountAmount = price * (discount / 100);
                const finalPrice = price - discountAmount;

                document.getElementById('result').innerHTML = `
                    Nova cena: <span class="text-green-400">${finalPrice.toFixed(2)} RSD</span><br/>
                    Ušteda: <span class="text-red-400">${discountAmount.toFixed(2)} RSD</span>
                `;
            });

            // Kalkulator vremena za završetak zadatka
            document.getElementById('timeCalcBtn').addEventListener('click', () => {
                const availableHours = parseFloat(document.getElementById('availableHours').value);
                const taskTime = parseInt(document.getElementById('taskTime').value);
                const breakMinutes = parseInt(document.getElementById('breakMinutes').value);

                if (isNaN(availableHours) || availableHours <= 0) {
                    alert('Molimo unesi validan broj sati na raspolaganju.');
                    return;
                }
                if (isNaN(taskTime) || taskTime <= 0) {
                    alert('Molimo unesi validno vreme za jedan zadatak (u minutima).');
                    return;
                }
                if (isNaN(breakMinutes) || breakMinutes < 0) {
                    alert('Molimo unesi validno ukupno vreme pauza (u minutima).');
                    return;
                }

                const totalAvailableMinutes = availableHours * 60;
                const workingMinutes = totalAvailableMinutes - breakMinutes;

                if (workingMinutes <= 0) {
                    alert('Ukupno vreme pauza ne može biti veće ili jednako ukupnom vremenu na raspolaganju.');
                    return;
                }

                const tasksDone = Math.floor(workingMinutes / taskTime);

                document.getElementById('timeResult').innerHTML = `
                    Možete završiti <span class="text-green-500">${tasksDone}</span> zadataka u ${availableHours} sati uz pauze od ${breakMinutes} minuta.
                `;
            });
        });
    </script>
</x-filament::page>
