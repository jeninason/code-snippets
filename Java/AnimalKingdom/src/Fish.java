
public abstract class Fish extends Animal implements WaterDweller {

	public static final String FISH_DESCRIPTION = "Fish";
	public static final boolean FISH_BREATHE_AIR = false;
	public static final boolean FISH_WARM_BLOOD = false;
	public static final BirthType FISH_BIRTH_TYPE = BirthType.LAYS_EGGS;
	
	public Fish(int id, String name) {
		super(id,name,FISH_BIRTH_TYPE);
	}
	
	public Fish(int id, String name, BirthType birthType) {
		super(id, name, birthType);
	}

	@Override
	public boolean isWarmBlooded() {
		return FISH_WARM_BLOOD; //fish are cold blooded
	}
	//no setter for isWarmBlooded as fish are all cold blooded
	
	@Override
	public String getDescription() {
		return FISH_DESCRIPTION + " (lives in water, "+
				(breathesAir() ? "breathes air" : "does not breathe air")+
				")\t";
	}

	public boolean breathesAir() {
		return FISH_BREATHE_AIR;
	}
	
}
