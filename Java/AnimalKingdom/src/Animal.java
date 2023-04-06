/* ADD THE EQUALS METHOD AND IMPLEMENT THE COMPARABLE INTERFACE */
/* MAKE NO OTHER CHANGES TO THIS FILE */

public abstract class Animal implements Comparable<Animal> {
	
	private int id;
	private String name;
	private BirthType birthType;

	public static final String ANIMAL_DESCRIPTION = "Animal";

	public Animal(int id, String name, BirthType birthType) {
		this.id = id;
		this.name = name;
		this.birthType = birthType;
	}
	
	
	// information about birth type
	public final boolean laysEggs() {
		return birthType==BirthType.LAYS_EGGS;
	}
	public final boolean hasLiveBirth() {
		return birthType==BirthType.LIVE_BIRTH;
	}
	public final BirthType getBirthType() {
		return birthType;
	}

	// getters (no setters provided)
	public final int getId() {
		return id;
	}

	public final String getName() {
		return name;
	}

	// toString method will create text with name, id, birth type, warm/cold blooded, and the description
	@Override
	public final String toString() {
		return name + " (id=" + id + "):\t" + birthType.toString() + "\t" +
				(isWarmBlooded() ? "Warm Blooded" : "Cold Blooded") + "\t" +
				getDescription();
	}

	// abstract methods to implement in child classes
	public abstract boolean isWarmBlooded();
	public abstract String getDescription();

	@Override
	public boolean equals(Object obj) {
		if (obj instanceof Animal) {
			Animal otherAnimal = (Animal) obj;
			boolean sameName = this.getName().equalsIgnoreCase(otherAnimal.getName());
			boolean sameId = this.getId() == otherAnimal.getId();

			return sameId && sameName;
			
		}
		return false;
		
	}

	@Override
	public int compareTo(Animal otherAnimal) {
		 return name.compareToIgnoreCase(otherAnimal.getName());
	}
		
	
	
}