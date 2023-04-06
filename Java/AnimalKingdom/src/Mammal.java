
public abstract class Mammal extends Animal {

	public static final BirthType MAMMAL_BIRTH_TYPE = BirthType.LIVE_BIRTH;
	public static final boolean MAMMAL_WARM_BLOODED = true;
	
	public static final String MAMMAL_DESCRIPTION = "Mammal";

	public Mammal(int id, String name) {
		super(id, name, MAMMAL_BIRTH_TYPE);
	}
		
	public Mammal(int id, String name, BirthType birthType) {
		super(id, name, birthType); //allow input for birthType for those that vary
	}
	@Override
	public boolean isWarmBlooded() {
		return MAMMAL_WARM_BLOODED;
	}
	//no setter for isWarmBlooded because all are warm-blooded

	@Override
	public String getDescription() {
		return MAMMAL_DESCRIPTION + "\t";
	}

}
