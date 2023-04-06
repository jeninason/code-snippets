
public abstract class Bird extends Animal implements Winged {

	public static final String BIRD_DESCRIPTION = "Bird";
	public static final boolean BIRD_CAN_FLY = true;
	public static final boolean BIRD_WARM_BLOOD = true;
	
	public static final BirthType BIRD_BIRTH_TYPE = BirthType.LAYS_EGGS;
	
	public Bird(int id, String name) {
		super(id,name,BIRD_BIRTH_TYPE);
	}
	
	@Override
	public boolean isWarmBlooded() {
		return BIRD_WARM_BLOOD; 
	}

	@Override
	public String getDescription() {
		return BIRD_DESCRIPTION	+ " (has wings)\t"; 
	}

	public boolean canFly() {
		return BIRD_CAN_FLY;
	}
		
}
