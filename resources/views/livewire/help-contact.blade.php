<div>
    <!-- Help Modal -->
    <div x-show="$wire.showModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="$wire.closeModal()"></div>

            <!-- Center positioning element -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <!-- Modal panel -->
            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-gray-800 sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                 @click.stop>

                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                            Help & Support
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Need help or want to report an issue? We're here to assist you.
                        </p>

                        <form wire:submit="submit" class="mt-6 space-y-4">
                            <!-- Type Selection -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Type of Request
                                </label>
                                <select wire:model="type" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="help">General Help</option>
                                    <option value="report">Report Content</option>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature_request">Feature Request</option>
                                </select>
                                @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Priority
                                </label>
                                <select wire:model="priority" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                    <option value="low">Low</option>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                @error('priority') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Subject -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Subject
                                </label>
                                <input type="text"
                                       wire:model="subject"
                                       placeholder="Brief description of your request"
                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                                @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea wire:model="description"
                                          rows="4"
                                          placeholder="Please provide detailed information about your request or issue"
                                          class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md resize-none dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></textarea>
                                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="flex flex-col mt-6 space-y-3 sm:flex-row sm:justify-end sm:space-x-3 sm:space-y-0">
                                <button type="button"
                                        wire:click="closeModal"
                                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md sm:w-auto dark:border-gray-600 dark:text-gray-300 dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md sm:w-auto hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
