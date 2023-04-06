import java.util.*;

public class AnimalKingdomDriver {
	
	private static boolean passesAllTests = true;
	
	public static void main(String[] args) {
		
		ArrayList<Animal> animalList = new ArrayList<Animal>();
		animalList.add(new Goldfish(1, "Goldie Goldfishy"));
		animalList.add(new Goldfish(2, "Blingy Gold Gold"));
		animalList.add(new GreatWhiteShark(3, "Toothy the Shark"));
		animalList.add(new Parakeet(4, "Tweety Parakeety"));
		animalList.add(new Parakeet(5, "Pretty Bird Pretty"));
		animalList.add(new CaliforniaCondor(6, "Cali Condor the Great"));
		animalList.add(new Ostrich(7, "Head in the Sand Hallie"));
		animalList.add(new BelugaWhale(8, "BabyBeluga O\'BabyBeluga"));
		animalList.add(new BlueWhale(9, "Spouty the Blue Whale"));
		animalList.add(new Elephant(10, "Never Irrelephant"));
		animalList.add(new DuckbilledPlatypus(11, "Donald Duck E. Platt"));
		animalList.add(new Horse(12, "Horsey McHorseface"));
		animalList.add(new Horse(13, "Mr. Ed the Fourth"));
//		animalList.add(new SouthernCassowary(14, "KeeWee"));
		
		Collections.sort(animalList); 
		// note the code won't compile until you implement Comparable!
		// after you implement Comparable, uncomment the sorting line
		
		System.out.println("------------------------------TEST ALL ANIMALS AND COMPARABLE IMPLEMENTATION------------------------------");
		System.out.println("Should print 13 animals, ordered in alphabetic order by name:\n");
		for(Animal animal : animalList) {
			System.out.println(animal);
		}
		if(animalList.get(0).getId()!=8 || animalList.get(animalList.size()-1).getId()!=4) {
			System.out.println("**********TEST FAILED: list is not sorted correctly.");
			System.out.println("          Make sure you have un-commented out line 24 that sorts the list.");
			passesAllTests = false;
		}

		System.out.println("\n\n------------------------------TEST WARM/COLD BLOODED------------------------------");
		int numWarmBlooded = 0, numColdBlooded = 0;
		for(Animal animal : animalList) {
			if(animal.isWarmBlooded()) {
				numWarmBlooded++;
			} else {
				numColdBlooded++;
			}
		}
		System.out.println("Number of warm-blooded animals:\n\tExpected=10\n\t  Actual=" + numWarmBlooded);
		System.out.println("Number of cold-blooded animals:\n\tExpected=3\n\t  Actual=" + numColdBlooded);

		if(numWarmBlooded!=10 || numColdBlooded!=3) {
			System.out.println("**********TEST FAILED: error in warm-blooded/cold-blooded implementation.");
			passesAllTests = false;
		}

	System.out.println("\nAll warm-blooded animals. Should print: " );
	System.out.println("BabyBeluga O\'BabyBeluga   Cali Condor the Great   Donald Duck E. Platt   Head in the Sand Hallie   Horsey McHorseface   Mr. Ed the Fourth   Never Irrelephant   Pretty Bird Pretty   Spouty the Blue Whale   Tweety Parakeety");
		for(Animal animal : animalList) {
			if(animal.isWarmBlooded()) {
				System.out.print(animal.getName() + "   ");
			}
		}
		System.out.println();	
		System.out.println("\nAll cold-blooded animals. Should print: " );
		System.out.println("Blingy Gold Gold   Goldie Goldfishy   Toothy the Shark   ");
		for(Animal animal : animalList) {
			if(!animal.isWarmBlooded()) {
				System.out.print(animal.getName() + "   ");
			}
		}	
		System.out.println();
	
		System.out.println("\n\n------------------------------TEST BIRTH TYPE------------------------------");
		int numLiveBirth = 0, numEggLaying = 0;
		for(Animal animal : animalList) {
			if(animal.hasLiveBirth()) {
				numLiveBirth++;
			} 
			if(animal.laysEggs()){
				numEggLaying++;
			}
		}
		System.out.println("Number of warm-blooded animals:\n\tExpected=6\n\t  Actual=" + numLiveBirth);
		System.out.println("Number of cold-blooded animals:\n\tExpected=7\n\t  Actual=" + numEggLaying);

		if(numLiveBirth!=6 || numEggLaying!=7) {
			System.out.println("**********TEST FAILED: error in birth type implementation.");
			passesAllTests = false;
		}

		System.out.println("\nAll live birth animals. Should print: ");
		System.out.println("BabyBeluga O\'BabyBeluga   Horsey McHorseface   Mr. Ed the Fourth   Never Irrelephant   Spouty the Blue Whale   Toothy the Shark   " );
		for(Animal animal : animalList) {
			if(animal.hasLiveBirth()) {
				System.out.print(animal.getName() + "   ");
			}
		}
		System.out.println();
		
		System.out.println("\nAll egg laying animals. Should print: ");
		System.out.println("Blingy Gold Gold   Cali Condor the Great   Donald Duck E. Platt   Goldie Goldfishy   Head in the Sand Hallie   Pretty Bird Pretty   Tweety Parakeety   " );
		for(Animal animal : animalList) {
			if(animal.laysEggs()) {
				System.out.print(animal.getName() + "   ");
			}
		}	
		System.out.println();
			
		System.out.println("\n\n------------------------------TEST EQUALS METHOD------------------------------");
		Animal testAnimal = new BlueWhale(9, "Spouty the Blue Whale");

		// parameter 1: the animal list
		// parameter 2: the test animal to see if the list contains that animal
		//              the contains method uses the equals method
		// parameter 3: the expected result
		testEqualsMethod(animalList, testAnimal, true);
				
		testAnimal = new BlueWhale(9, "SPOUTY THE BLUE WHALE");
		testEqualsMethod(animalList, testAnimal, true);

		testAnimal = new BlueWhale(9, new String("Spouty the Blue Whale"));
		testEqualsMethod(animalList, testAnimal, true);

		testAnimal = new BlueWhale(19, "Spouty the Blue Whale");
		testEqualsMethod(animalList, testAnimal, false);

		testAnimal = new BlueWhale(9, "Sprouty the Red Whale");
		testEqualsMethod(animalList, testAnimal, false);
	
		System.out.println("\n\n------------------------------TEST ANIMALS WITH WINGS------------------------------");
		int numWinged = 0, numWingedCanFly = 0, numWingedCannotFly = 0;
		for(Animal animal : animalList) {
			if(animal instanceof Winged) {
				numWinged++;
				Winged winged = (Winged) animal;
				boolean canFly = winged.canFly();
				if(canFly) {
					numWingedCanFly++;
				} else {
					numWingedCannotFly++;
				}
			}
		}
		System.out.println("Number of winged animals:\n\tExpected=4\n\t  Actual=" + numWinged);
		System.out.println("Number of winged animals that can fly:\n\tExpected=3\n\t  Actual=" + numWingedCanFly);
		System.out.println("Number of winged animals that cannot fly:\n\tExpected=1\n\t  Actual=" + numWingedCannotFly);
		if(numWinged!=4 || numWingedCanFly!=3 || numWingedCannotFly!=1) {
			System.out.println("**********TEST FAILED: error in winged implementation.");
			passesAllTests = false;
		}
		
		System.out.println("\nShould print 4 lines: Cali can fly, Head in Sand flightless, Pretty can fly, and Tweety can fly.");
		for(Animal animal : animalList) {
			if(animal instanceof Winged) {
				Winged winged = (Winged) animal;
				String fly = winged.canFly() ? " can fly!" : " is flightless.";
				System.out.println("\t" + animal.getName() + fly);
			}
		}


 		// PART C CODE: UNCOMMENT THIS SECTION WHEN YOU ARE READY TO WRITE AND TEST YOUR PART C DRIVER CODE
 		 
		System.out.println("\n\n------------------------------TEST WATER DWELLERS------------------------------");
		System.out.println("-----------------YOUR PART C CODE HERE!-----------------");
		int numWaterDwellers = 0, numWaterDwellersBreatheAir = 0, numWaterDwellersBreatheWater = 0;
		
		// calculate the number of water dwellers, the number of water dwellers that breathe air, 
		// and the number of water dwellers that do not breathe air (meaning they breathe water)
		// YOUR PART C CODE HERE
		for (Animal animal : animalList) {

            if (animal instanceof WaterDweller) {
                numWaterDwellers++;
                if (((WaterDweller) animal).breathesAir() == true) {
                    numWaterDwellersBreatheAir++;
                } else if (((WaterDweller) animal).breathesAir() == false) {
                    numWaterDwellersBreatheWater++;
                }
            }
        }
		System.out.println("Number of water dwellers:\n\tExpected=5\n\t  Actual=" + numWaterDwellers);
		System.out.println("Number of water dwellers that breathe air:\n\tExpected=2\n\t  Actual=" + numWaterDwellersBreatheAir);
		System.out.println("Number of water dwellers that breathe water:\n\tExpected=3\n\t  Actual=" + numWaterDwellersBreatheWater);
		
		if(numWaterDwellers!=5 || numWaterDwellersBreatheAir!=2 || numWaterDwellersBreatheWater!=3) {
			System.out.println("**********TEST FAILED: error in driver code that counts water dwellers.");
			passesAllTests = false;
		}
		
		// print out the names of the water dwellers and include whether they breathe air
		System.out.println("\nShould print 5 lines: Beluga breathes air, Blingy breathes water, Goldie breathes water, Spouty breathes air, and Toothy breathes water.\n");		
		// YOUR PART C CODE HERE
		for(Animal animal : animalList) {
			if(animal instanceof WaterDweller) {
				System.out.print(animal.getName() + " lives in the water" + (((WaterDweller) animal).breathesAir() ? " but breathes air!" : " and breathes water.") + "\n");
			}
		}
		
		
		System.out.println("\n\n------------------------------TEST ENDANGERED ANIMALS------------------------------");
		System.out.println("-----------------YOUR PART C CODE HERE!-----------------");
		int numEndangered = 0;
		// YOUR PART C CODE HERE
		for (Animal animal : animalList) {

            if (animal instanceof Endangered) {
                numEndangered++;
            }
		}
		System.out.println("Number of endangered animals:\n\tExpected=4\n\t  Actual=" + numEndangered);

		if(numEndangered!=4) {
			System.out.println("**********TEST FAILED: error in driver code that counts water dwellers.");
			passesAllTests = false;
		}
		
		// print out the conservation information for the endangered animals
		System.out.println("\nShould print 4 lines for Cali, Irrelephant, Spouty, and Toothy.\n");
		// YOUR PART C CODE HERE
		for(Animal animal : animalList) {
			if(animal instanceof Endangered) {
				System.out.print(animal.getName() + " says: \"");
				((Endangered) animal).displayConservationInformation();
				System.out.print("\"\n");
			}
		}
	
		
		
		if(passesAllTests) {
			System.out.println("\n\n----------Summary---------- \nAll automated tests have passed.\nManually view all expected output anove to make sure it matches.\nManually review your code for style.");
		} else {
			System.out.flush();
			System.err.println("\n\n**********Summary********** ERROR: There is failure in at least one automated test. Review the output above for details.");
		}

	}

	
	
	
	

	/*----------------------------------------------------------------------------------------------------------*/
	/* TESTER METHODS */
	/*----------------------------------------------------------------------------------------------------------*/
	/*
	 * The methods below are designed to help support the tests cases run from main. You don't
	 * need to use, modify, or understand these methods. You can safely ignore them. :) 
	 */
	 public static void testEqualsMethod(List<Animal> animalList, Animal testAnimal, boolean expectedResult) {
		boolean actualResult = animalList.contains(testAnimal);
		System.out.println("\nTesting if list contains " + testAnimal.getName() + "\tID=" + testAnimal.getId());
		System.out.println("\tExpected= " + expectedResult);
		System.out.println("\t  Actual= " + actualResult);
		if(expectedResult != actualResult) {
			System.out.println("**********TEST FAILED: error in equals method implementation.");
			passesAllTests = false;
		}
	 }
}
