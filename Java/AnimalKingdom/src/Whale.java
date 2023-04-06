
public abstract class Whale extends Mammal implements WaterDweller {

	public static final String WHALE_DESCRIPTION = "Whale";
	
	public Whale(int id, String name) {
		super(id, name); //no need to have an option for birth type for whales
	}
	
	@Override
	public String getDescription() {
		return super.getDescription() + WHALE_DESCRIPTION +
				" (lives in water, "+
				(breathesAir() ? "breathes air" : "does not breathe air")+
				")\t";
	}
	
	@Override
	public boolean breathesAir() {
		return true;
	}

}
